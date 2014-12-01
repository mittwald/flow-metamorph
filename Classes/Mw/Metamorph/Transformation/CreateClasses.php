<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;


class CreateClasses extends AbstractTransformation
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;


    /**
     * @var MorphConfigurationRepository
     * @Flow\Inject
     */
    protected $morphRepository;



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out)
    {
        $classMappingContainer = $configuration->getClassMappingContainer();
        $packageClassCount = [];

        foreach ($classMappingContainer->getClassMappings() as $classMapping)
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

        $this->morphRepository->update($configuration);

        foreach ($packageClassCount as $package => $count)
        {
            $this->log('<comment>%d</comment> classes written to package <comment>%s</comment>.', [$count, $package]);
        }
    }
}