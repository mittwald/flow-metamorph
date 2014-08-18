<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use TYPO3\Flow\Annotations as Flow;


class PackageClassRewrite implements Transformation
{



    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;


    /** @var \PhpParser\Parser */
    private $parser;


    /** @var \PhpParser\PrettyPrinterAbstract */
    private $printer;


    /**
     * @var array
     */
    private $settings;


    /** @var \PhpParser\NodeTraverser */
    private $traverser;



    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }



    public function initializeObject()
    {
        $this->parser  = new Parser(new Lexer());
        $this->printer = new Standard();
    }



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $classMappings = $state->getClassMapping();

        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());

        foreach ($this->settings['visitors'] as $visitor)
        {
            if (!class_exists($visitor))
            {
                $visitorClass = 'Mw\\Metamorph\\Transformation\\RewriteNodeVisitors\\' . $visitor;

                /** @var \Mw\Metamorph\Transformation\RewriteNodeVisitors\AbstractVisitor $visitor */
                $visitor = $this->objectManager->get($visitorClass);
                $visitor->setClassMap($classMap);

                $this->traverser->addVisitor($visitor);
                $out->outputLine('  - Adding node visitor <i>%s</i>.', [$visitorClass]);
            }
        }

        foreach ($classMappings->getClassMappings() as $classMapping)
        {
            $this->replaceExtbaseClassnamesInClass($classMapping, $out);
        }
    }



    private function replaceExtbaseClassnamesInClass(ClassMapping $classMapping, OutputInterface $out)
    {
        $filecontent = file_get_contents($classMapping->getTargetFile());
        $syntaxTree  = $this->parser->parse($filecontent);

        $syntaxTree = $this->traverser->traverse($syntaxTree);

        file_put_contents($classMapping->getTargetFile(), $this->printer->prettyPrintFile($syntaxTree));
    }
}