<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;


use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\TcaLoader;
use Mw\Metamorph\Transformation\DatabaseMigration\Visitor\FullMigrationVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use TYPO3\Flow\Annotations as Flow;


class FullMigrationStrategy implements MigrationStrategyInterface
{



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
     * @var \SplPriorityQueue
     */
    private $taskQueue;



    public function __construct()
    {
        $this->parser  = new Parser(new Lexer());
        $this->printer = new Standard();
    }



    public function execute(MorphConfiguration $configuration)
    {
        $this->tca = new Tca();

        $this->loadTca($configuration);
        $this->processClasses($configuration);
    }



    public function setDeferredTaskQueue(\SplPriorityQueue $queue)
    {
        $this->taskQueue = $queue;
    }



    private function loadTca(MorphConfiguration $configuration)
    {
        $packageMappingContainer = $configuration->getPackageMappingContainer();
        $packageMappingContainer->assertReviewed();

        foreach ($packageMappingContainer->getPackageMappings() as $packageMapping)
        {
            $this->tcaLoader->loadTcaForPackage($packageMapping, $this->tca);
        }
    }



    private function processClasses(MorphConfiguration $configuration)
    {
        /** @var ClassDefinition[] $classes */
        $classes = array_merge(
            $this->classDefinitionContainer->findByFact('isEntity', TRUE),
            $this->classDefinitionContainer->findByFact('isValueObject', TRUE)
        );

        foreach ($classes as $class)
        {
            $file    = $class->getClassMapping()->getTargetFile();
            $content = file_get_contents($file);
            $stmts   = $this->parser->parse($content);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver());
            $traverser->addVisitor(new FullMigrationVisitor($this->tca, $this->taskQueue));

            $stmts = $traverser->traverse($stmts);

            $content = $this->printer->prettyPrintFile($stmts);
            file_put_contents($file, $content);
        }
    }



}