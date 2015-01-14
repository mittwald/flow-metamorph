<?php
namespace Mw\Metamorph\Transformation;

use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Parser\ParserInterface;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\Lexer;
use PhpParser\NodeVisitor\NameResolver;
use TYPO3\Flow\Annotations as Flow;

class GenericAstVisitorTransformation extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var string
	 * @Flow\Inject(setting="defaults.transformationVisitorNamespace")
	 */
	protected $defaultNamespace;

	/**
	 * @var ParserInterface
	 * @Flow\Inject
	 */
	protected $parser;

	/**
	 * @var \PhpParser\PrettyPrinterAbstract
	 * @Flow\Inject
	 */
	protected $printer;

	/**
	 * @var \PhpParser\NodeTraverser
	 * @Flow\Inject
	 */
	protected $traverser;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$classMappingContainer = $configuration->getClassMappingContainer();
		$taskQueue             = new TaskQueue();

		$this->traverser->addVisitor(new NameResolver());

		foreach ($this->settings['visitors'] as $visitorClass) {
			if (!class_exists($visitorClass)) {
				$visitorClass = $this->defaultNamespace . $visitorClass;
			}

			/** @var \Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor $visitor */
			$visitor = new $visitorClass();
			$visitor->setMorphConfiguration($configuration);
			$visitor->setClassMap($classMappingContainer);
			$visitor->setDeferredTaskQueue($taskQueue);

			$this->traverser->addVisitor($visitor);
			$this->log('Adding node visitor <info>%s</info>.', [$visitorClass]);
		}

		$classMappings = $classMappingContainer->getClassMappingsByFilter(function(ClassMapping $c) {
			return $c->getAction() === ClassMapping::ACTION_MORPH;
		});

		$this->startProgress('Refactoring classes', count($classMappings));
		foreach ($classMappings as $classMapping) {
			$this->refactorClass($classMapping);
			$this->advanceProgress();
		}
		$this->finishProgress();

		$this->startProgress('Cleaning up', 0);
		$taskQueue->executeAll($configuration, function ($m) { $this->advanceProgress(); });
		$this->finishProgress();
	}

	private function refactorClass(ClassMapping $classMapping) {
		if (FALSE == $classMapping->getTargetFile()) {
			throw new HumanInterventionRequiredException("No target file was set for class <comment>{$classMapping->getOldClassName()}</comment>.");
		}

		$syntaxTree = $this->parser->parseFile($classMapping->getTargetFile());
		$syntaxTree = $this->traverser->traverse($syntaxTree);

		file_put_contents($classMapping->getTargetFile(), $this->printer->prettyPrintFile($syntaxTree));
	}
}
