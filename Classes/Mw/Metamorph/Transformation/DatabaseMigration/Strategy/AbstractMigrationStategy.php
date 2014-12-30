<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\TcaLoader;
use Mw\Metamorph\Transformation\Progressible;
use Mw\Metamorph\Transformation\ProgressibleTrait;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use TYPO3\Flow\Annotations as Flow;

/**
 * Abstract base class for database migration strategies.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Strategy
 */
abstract class AbstractMigrationStategy implements MigrationStrategyInterface, Progressible {

	use ProgressibleTrait;

	/**
	 * @var TcaLoader
	 * @Flow\Inject
	 */
	protected $tcaLoader;

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var Tca
	 */
	protected $tca;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var PrettyPrinterAbstract
	 */
	private $printer;

	/**
	 * @var TaskQueue
	 */
	protected $taskQueue;

	public function __construct() {
		$this->parser  = new Parser(new Lexer());
		$this->printer = new Standard();
	}

	public function execute(MorphConfiguration $configuration) {
		$this->tca = new Tca();

		$this->loadTca($configuration);
		$this->processClasses($configuration);
	}

	public function setDeferredTaskQueue(TaskQueue $queue) {
		$this->taskQueue = $queue;
	}

	private function loadTca(MorphConfiguration $configuration) {
		$packageMappingContainer = $configuration->getPackageMappingContainer();
		$packageMappingContainer->assertReviewed();

		$this->startProgress('Loading TCA', count($packageMappingContainer->getPackageMappings()));
		foreach ($packageMappingContainer->getPackageMappings() as $packageMapping) {
			$this->tcaLoader->loadTcaForPackage($packageMapping, $this->tca);
			$this->advanceProgress();
		}
		$this->finishProgress();
	}

	private function processClasses(MorphConfiguration $configuration) {
		/** @var ClassDefinition[] $classes */
		$classes = array_merge(
			$this->classDefinitionContainer->findByFact('isEntity', TRUE),
			$this->classDefinitionContainer->findByFact('isValueObject', TRUE)
		);

		$this->startProgress('Refactoring classes', count($classes));
		foreach ($classes as $class) {
			$file    = $class->getClassMapping()->getTargetFile();
			$content = file_get_contents($file);
			$stmts   = $this->parser->parse($content);

			$traverser = new NodeTraverser();
			$traverser->addVisitor(new NameResolver());
			$traverser->addVisitor($this->getMigrationVisitor());

			$stmts = $traverser->traverse($stmts);

			$content = $this->printer->prettyPrintFile($stmts);
			file_put_contents($file, $content);
			$this->advanceProgress();
		}
		$this->finishProgress();
	}

	/**
	 * @return NodeVisitor
	 */
	abstract protected function getMigrationVisitor();

}