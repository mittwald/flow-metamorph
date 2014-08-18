<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;


class CreateClasses extends AbstractTransformation
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $classMappings     = $state->getClassMapping();
        $packageClassCount = [];

        foreach ($classMappings->getClassMappings() as $classMapping)
        {
            if ($classMapping->getAction() !== ClassMapping::ACTION_MORPH)
            {
                continue;
            }

            $package      = $this->packageManager->getPackage($classMapping->getPackage());
            $source       = file_get_contents($classMapping->getSourceFile());
            $newClassName = $classMapping->getNewClassName();

            $relativeFilename = str_replace('\\', '/', $newClassName) . '.php';
            $absoluteFilename = $package->getClassesPath() . '/' . $relativeFilename;

            Files::createDirectoryRecursively(dirname($absoluteFilename));

            file_put_contents($absoluteFilename, $source);

            if (!isset($packageClassCount[$package->getPackageKey()]))
            {
                $packageClassCount[$package->getPackageKey()] = 0;
            }
            $packageClassCount[$package->getPackageKey()]++;

            $classMapping->setTargetFile($absoluteFilename);
        }

        $state->updateClassMapping($classMappings);

        foreach ($packageClassCount as $package => $count)
        {
            $out->outputLine('  - <b>%d</b> classes written to package <i>%s</i>.', [$count, $package]);
        }
    }
}