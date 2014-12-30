<?php
namespace Mw\Metamorph\Transformation;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\Analyzer\AnalyzerVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use TYPO3\Flow\Annotations as Flow;

class AnalyzeClasses extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var Parser
	 * @Flow\Inject
	 */
	protected $parser;

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$classMappingContainer = $configuration->getClassMappingContainer();
		$this->startProgress('Analyzing classes', count($classMappingContainer->getClassMappings()));
		foreach ($classMappingContainer->getClassMappings() as $classMapping) {
			$this->analyzeClass($classMapping, $classMappingContainer);
			$this->advanceProgress();
		}
		$this->finishProgress();
	}

	private function analyzeClass(ClassMapping $mapping, ClassMappingContainer $container) {
		$code  = file_get_contents($mapping->getTargetFile());
		$stmts = $this->parser->parse($code);

		$traverser = new NodeTraverser();
		$traverser->addVisitor(new NameResolver());
		$traverser->addVisitor(new AnalyzerVisitor($container));

		$traverser->traverse($stmts);
	}

}
