<?php
namespace Mw\Metamorph\Transformation;



use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\ClassInventory\ClassFinderVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Annotations as Flow;


class ClassInventory extends AbstractTransformation
{



    /** @var ClassMappingContainer */
    private $classMappings = NULL;


    /**
     * @var \PhpParser\Parser
     */
    private $parser;


    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;



    public function initializeObject()
    {
        $this->parser = new Parser(new Lexer());
    }



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out)
    {
        $packageMappings     = $state->getPackageMapping(TRUE);
        $this->classMappings = $state->getClassMapping(FALSE);

        foreach ($packageMappings as $packageMapping)
        {
            if ($packageMapping->getAction() === PackageMapping::ACTION_MORPH)
            {
                $this->readClassesFromExtension($packageMapping, $out);
            }
        }

        $state->updateClassMapping($this->classMappings);
    }



    private function readClassesFromExtension(
        PackageMapping $packageMapping,
        OutputInterface $out
    ) {
        $directoryIterator = new \RecursiveDirectoryIterator($packageMapping->getFilePath());
        $iteratorIterator  = new \RecursiveIteratorIterator($directoryIterator);
        $regexIterator     = new \RegexIterator($iteratorIterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $classList = new \ArrayObject();

        foreach ($regexIterator as $match)
        {
            $filename = $match[0];
            $this->readClassesFromFile($filename, $classList, $out);
        }

        $out->writeln(
            vsprintf(
                '  - <b>%d</b> classes found in EXT:<i>%s</i>.',
                [count($classList), $packageMapping->getExtensionKey()]
            )
        );

        foreach ($classList as $className => $filename)
        {
            if (FALSE === $this->classMappings->hasClassMapping($className))
            {
                $classMapping = new ClassMapping(
                    $filename, $className, $this->guessMorphedClassName(
                        $className,
                        $filename,
                        $packageMapping
                    ), $packageMapping->getPackageKey()
                );

                $this->classMappings->addClassMapping($classMapping);
            }
        }
    }



    private function readClassesFromFile($filename, \ArrayAccess $classList, OutputInterface $out)
    {
        $fileContent = file_get_contents($filename);
        $syntaxTree  = $this->parser->parse($fileContent);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassFinderVisitor($classList, $filename));

        foreach ($this->settings['visitors'] as $visitorClassName)
        {
            if (FALSE === class_exists($visitorClassName))
            {
                $visitorClassName = 'Mw\\Metamorph\\Transformation\\ClassInventory\\' . $visitorClassName;
            }

            $visitor = $this->objectManager->get($visitorClassName);
            if ($visitor instanceof NodeVisitor)
            {
                $traverser->addVisitor($visitor);
            }
        }

        $traverser->traverse($syntaxTree);
    }



    private function guessMorphedClassName($className, $filename, PackageMapping $packageMapping)
    {
        $newPackageNamespace       = str_replace('.', '\\', $packageMapping->getPackageKey());
        $filenameInferredNamespace = str_replace('/', '\\', str_replace(['class.', '.php'], '', $filename));

        $actualNamespaceParts   = explode('\\', $className);
        $inferredNamespaceParts = explode('\\', $filenameInferredNamespace);

        $common = array_intersect($actualNamespaceParts, $inferredNamespaceParts);
        return $newPackageNamespace . '\\' . implode('\\', $common);
    }
}