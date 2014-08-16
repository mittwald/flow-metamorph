<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;


class CreateClasses implements Transformation
{


    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $packageMap = $state->readYamlFile('PackageMap', TRUE);
        $classMap   = $state->readYamlFile('ClassMap', TRUE);

        $packageClassCount = [];

        foreach ($classMap['classes'] as $oldClassName => $classConfiguration)
        {
            if ($classConfiguration['action'] !== 'MORPH')
            {
                continue;
            }

            $package      = $this->packageManager->getPackage($classConfiguration['package']);
            $source       = file_get_contents($classConfiguration['source']);
            $newClassName = $classConfiguration['newClassname'];

            $relativeFilename = str_replace('\\', '/', $newClassName) . '.php';
            $absoluteFilename = $package->getClassesPath() . '/' . $relativeFilename;

            Files::createDirectoryRecursively(dirname($absoluteFilename));

            file_put_contents($absoluteFilename, $source);

            if (!isset($packageClassCount[$package->getPackageKey()]))
            {
                $packageClassCount[$package->getPackageKey()] = 0;
            }
            $packageClassCount[$package->getPackageKey()]++;

            $classMap['classes'][$oldClassName]['target'] = $absoluteFilename;
        }

        $state->writeYamlFile('ClassMap', $classMap);

        foreach ($packageClassCount as $package => $count)
        {
            $out->outputLine('  - <b>%d</b> classes written to package <i>%s</i>.', [$count, $package]);
        }
    }
}