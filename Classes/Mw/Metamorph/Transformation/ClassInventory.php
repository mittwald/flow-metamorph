<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Io\OutputInterface;
use Mw\Metamorph\Transformation\ClassInventory\ClassFinderVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;


class ClassInventory extends AbstractTransformation
{



    private $classMap;


    /**
     * @var \PhpParser\Parser
     */
    private $parser;



    public function initializeObject()
    {
        $this->parser = new Parser(new Lexer());
    }



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $packageMap     = $state->readYamlFile('PackageMap', TRUE);
        $this->classMap = $state->readYamlFile('ClassMap', FALSE);

        foreach ($packageMap['extensions'] as $extensionKey => $extensionConfiguration)
        {
            if ($extensionConfiguration['action'] === 'MORPH')
            {
                $this->readClassesFromExtension($configuration, $extensionKey, $extensionConfiguration, $out);
            }
        }

        $state->writeYamlFile('ClassMap', $this->classMap);
    }



    private function readClassesFromExtension(
        MorphConfiguration $configuration,
        $extensionKey,
        array $extensionConfiguration,
        OutputInterface $out
    ) {
        $extensionPath = $configuration->getSourceDirectory() . '/typo3conf/ext/' . $extensionKey;

        $directoryIterator = new \RecursiveDirectoryIterator($extensionPath);
        $iteratorIterator  = new \RecursiveIteratorIterator($directoryIterator);
        $regexIterator     = new \RegexIterator($iteratorIterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $classList = new \ArrayObject();

        foreach ($regexIterator as $match)
        {
            $filename = $match[0];
            $this->readClassesFromFile($filename, $classList);
        }

        $out->outputLine('  - <b>%d</b> classes found in EXT:<i>%s</i>.', [count($classList), $extensionKey]);

        foreach ($classList as $className => $filename)
        {
            if (!isset($this->classMap['classes']) || !array_key_exists($className, $this->classMap['classes']))
            {
                $this->classMap['reviewed']            = FALSE;
                $this->classMap['classes'][$className] = [
                    'source'       => $filename,
                    'action'       => 'MORPH',
                    'newClassname' => $this->guessMorphedClassName($className, $filename, $extensionConfiguration),
                    'package'      => $extensionConfiguration['packageKey']
                ];
            }
        }
    }



    private function readClassesFromFile($filename, \ArrayAccess $classList)
    {
        $filecontent = file_get_contents($filename);
        $syntaxTree  = $this->parser->parse($filecontent);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassFinderVisitor($classList, $filename));

        $traverser->traverse($syntaxTree);
    }



    private function guessMorphedClassName($className, $filename, $extensionConfiguration)
    {
        $newPackageNamespace       = str_replace('.', '\\', $extensionConfiguration['packageKey']);
        $filenameInferredNamespace = str_replace('/', '\\', str_replace(['class.', '.php'], '', $filename));

        $actualNamespaceParts   = explode('\\', $className);
        $inferredNamespaceParts = explode('\\', $filenameInferredNamespace);

        $common = array_intersect($actualNamespaceParts, $inferredNamespaceParts);
        return $newPackageNamespace . '\\' . implode('\\', $common);
    }
}