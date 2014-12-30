<?php
namespace Mw\Metamorph\Transformation;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\Task\Queue;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;

class PackageClassRewrite extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var \PhpParser\Parser
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

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out) {
		$classMappingContainer = $configuration->getClassMappingContainer();
		$taskQueue             = new TaskQueue();

		$this->traverser->addVisitor(new NameResolver());

		foreach ($this->settings['visitors'] as $visitorClass) {
			if (!class_exists($visitorClass)) {
				$visitorClass = 'Mw\\Metamorph\\Transformation\\RewriteNodeVisitors\\' . $visitorClass;
			}

			/** @var \Mw\Metamorph\Transformation\RewriteNodeVisitors\AbstractVisitor $visitor */
			$visitor = new $visitorClass();
			$visitor->setMorphConfiguration($configuration);
			$visitor->setClassMap($classMappingContainer);
			$visitor->setDeferredTaskQueue($taskQueue);

			$this->traverser->addVisitor($visitor);
			$this->log('Adding node visitor <info>%s</info>.', [$visitorClass]);
		}

		$this->startProgress('Refactoring classes', count($classMappingContainer->getClassMappings()));
		foreach ($classMappingContainer->getClassMappings() as $classMapping) {
			$this->refactorClass($classMapping, $out);
			$this->advanceProgress();
		}
		$this->finishProgress();

		$this->startProgress('Cleaning up', 0);
		$taskQueue->executeAll($configuration, function ($m) { $this->advanceProgress(); });
		$this->finishProgress();
	}

	private function refactorClass(ClassMapping $classMapping, OutputInterface $out) {
		$filecontent = file_get_contents($classMapping->getTargetFile());
		$syntaxTree  = $this->parser->parse($filecontent);

		$syntaxTree = $this->traverser->traverse($syntaxTree);

		file_put_contents($classMapping->getTargetFile(), $this->printer->prettyPrintFile($syntaxTree));
	}
}
