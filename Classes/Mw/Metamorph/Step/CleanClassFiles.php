<?php
namespace Mw\Metamorph\Step;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Parser\PHP\PHPParser;
use Mw\Metamorph\Transformation\AbstractTransformation;
use Mw\Metamorph\Transformation\Progressible;
use Mw\Metamorph\Transformation\ProgressibleTrait;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\PrettyPrinterAbstract;
use TYPO3\Flow\Annotations as Flow;

class CleanClassFiles extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var PHPParser
	 * @Flow\Inject
	 */
	protected $parser;

	/**
	 * @var PrettyPrinterAbstract
	 * @Flow\Inject
	 */
	protected $printer;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$mappings = $configuration->getClassMappingContainer()->getClassMappings();

		$this->startProgress('Cleaning class files', count($mappings));
		foreach ($mappings as $mapping) {
			$this->cleanClassFile($mapping);
			$this->advanceProgress();
		}
		$this->finishProgress();
	}

	private function cleanClassFile(ClassMapping $classMapping) {
		$nodes = $this->parser->parseFile($classMapping->getTargetFile());
		$nodes = $this->cleanNodeList($nodes);

		$code = $this->printer->prettyPrintFile($nodes);
		file_put_contents($classMapping->getTargetFile(), $code);
	}

	private function cleanNodeList(array $nodes) {
		$outputNodes = [];
		foreach ($nodes as $node) {
			if ($node instanceof Namespace_) {
				$node->stmts = $this->cleanNodeList($node->stmts);
				$outputNodes[] = $node;
			} elseif ($node instanceof Class_ || $node instanceof Interface_) {
				$outputNodes[] = $node;
			} elseif ($node instanceof Use_) {
				$outputNodes[] = $node;
			}
		}

		return $outputNodes;
	}
}