<?php
namespace Mw\Metamorph\Transformation;


use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageInterface;
use TYPO3\Flow\Utility\Files;


class CreateClasses extends AbstractTransformation
{



    use ProgressibleTransformation;


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
        $packageClassCount     = [];

        $this->startProgress('Migrating classes', count($classMappingContainer->getClassMappings()));

        foreach ($classMappingContainer->getClassMappings() as $classMapping)
        {
            if ($classMapping->getAction() !== ClassMapping::ACTION_MORPH)
            {
                continue;
            }

            $package      = $this->packageManager->getPackage($classMapping->getPackage());
            $source       = file_get_contents($classMapping->getSourceFile());
            $newClassName = new String($classMapping->getNewClassName());

            $relativeFilename = $newClassName->replace('\\', '/')->append('.php');
            $absoluteFilename = $this->getAbsoluteFilename($relativeFilename, $package);

            Files::createDirectoryRecursively(dirname($absoluteFilename));

            file_put_contents($absoluteFilename, $source);

            if (!isset($packageClassCount[$package->getPackageKey()]))
            {
                $packageClassCount[$package->getPackageKey()] = 0;
            }
            $packageClassCount[$package->getPackageKey()]++;

            $classMapping->setTargetFile($absoluteFilename);
            $this->advanceProgress();
        }

        $this->finishProgress();
        $this->morphRepository->update($configuration);

        foreach ($packageClassCount as $package => $count)
        {
            $this->log('<comment>%d</comment> classes written to package <comment>%s</comment>.', [$count, $package]);
        }
    }



    /**
     * Determines if a class contains a test case.
     *
     * @param \Helmich\Scalars\Types\String $relativeFilename The relative class file name.
     * @return bool TRUE if the class contains a test case, otherwise FALSE.
     */
    private function isClassTestCase(String $relativeFilename)
    {
        return $relativeFilename->strip('/')->contains('Tests/') || $relativeFilename->endsWidth('Test.php');
    }



    /**
     * Gets the absolute target filename for a class file.
     *
     * @param \Helmich\Scalars\Types\String $relativeFilename The relative class file name (auto-derived from class name).
     * @param PackageInterface              $package          The target package.
     * @return string The target filename.
     */
    private function getAbsoluteFilename(String $relativeFilename, PackageInterface $package)
    {
        if (FALSE === $this->isClassTestCase($relativeFilename))
        {
            return $package->getClassesPath() . '/' . $relativeFilename;
        }
        else
        {
            return (new String(''))
                ->append($package->getPackagePath())
                ->stripRight('/')
                ->append('/Tests/Unit/')
                ->append(
                    $relativeFilename
                        ->replace('Tests/', '')
                        ->replace((new String($package->getPackageKey()))->replace('.', '/')->append('/'), '')
                )
                ->toPrimitive();
        }
    }
}