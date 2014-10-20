<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\Analyzer\AnalyzerVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;


class AnalyzeClasses extends AbstractTransformation
{



    /**
     * @var Parser
     */
    private $parser;


    /**
     * @var ClassDefinitionContainer
     * @Flow\Inject
     */
    protected $classDefinitionContainer;



    public function __construct()
    {
        $this->parser = new Parser(new Lexer());
    }



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out)
    {
        $classMappingContainer = $configuration->getClassMappingContainer();
        $classMappingContainer->assertReviewed();

        foreach ($classMappingContainer->getClassMappings() as $classMapping)
        {
            $this->analyzeClass($classMapping, $classMappingContainer);
        }
    }



    private function analyzeClass(ClassMapping $mapping, ClassMappingContainer $container)
    {
        $code  = file_get_contents($mapping->getTargetFile());
        $stmts = $this->parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new AnalyzerVisitor($container));

        $traverser->traverse($stmts);
    }



}