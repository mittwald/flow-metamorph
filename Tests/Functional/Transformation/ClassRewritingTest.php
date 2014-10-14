<?php
namespace Mw\Metamorph\Tests\Functional\Transformation;


use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Transformation\Analyzer\AnalyzerVisitor;
use Mw\Metamorph\Transformation\RewriteNodeVisitors\AbstractVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Reflection\ReflectionService;
use TYPO3\Flow\Tests\FunctionalTestCase;


class ClassRewritingTest extends FunctionalTestCase
{



    /** @var Parser */
    private $parser;


    /**
     * @var ReflectionService
     */
    private $reflectionService;


    /**
     * @var PrettyPrinterAbstract
     */
    private $printer;



    public function setUp()
    {
        parent::setUp();

        $this->parser = new Parser(new Lexer());
        $this->printer = new Standard();
        $this->reflectionService = $this->objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
    }



    /**
     * @dataProvider getRefactoringTestCases
     */
    public function testClassIsCorrectlyRefactored($inputCode, $expectedOutputCode)
    {
        $inputTree          = $this->parser->parse($inputCode);
        $expectedOutputTree = $this->parser->parse($expectedOutputCode);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new AnalyzerVisitor());
        $traverser->traverse($inputTree);

        $classMap = new ClassMappingContainer();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        $visitorClasses = $this->reflectionService->getAllSubClassNamesForClass('Mw\\Metamorph\\Transformation\\RewriteNodeVisitors\\AbstractVisitor');
        foreach($visitorClasses as $visitorClass)
        {
            /** @var AbstractVisitor $visitor */
            $visitor = $this->objectManager->get($visitorClass);
            $visitor->setClassMap($classMap);
            $traverser->addVisitor($visitor);
        }

        $actualOutputTree = $traverser->traverse($inputTree);

        $this->assertCodeTreesEqual($expectedOutputTree, $actualOutputTree);
    }



    public function assertCodeTreesEqual(array $expected, array $actual)
    {
        $this->assertEquals(
            $this->printer->prettyPrint($expected),
            $this->printer->prettyPrint($actual)
        );
    }


    public function getRefactoringTestCases()
    {
        $dirPath = __DIR__ . '/Fixtures';

        $dirIter = new \RecursiveDirectoryIterator($dirPath);
        $iterIter = new \RecursiveIteratorIterator($dirIter);
        $regex = new \RegexIterator($iterIter, ',^.+\.ya?ml$,', \RecursiveRegexIterator::GET_MATCH);

        $data = [];

        foreach ($regex as $info)
        {
            $testCase = Yaml::parse(file_get_contents($info[0]));
            $data[] = [
                $testCase['input'],
                $testCase['output']
            ];
        }

        return $data;
    }

}