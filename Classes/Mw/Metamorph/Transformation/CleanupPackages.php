<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;


class CleanupPackages extends AbstractTransformation
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out)
    {
        $packageMappingContainer = $configuration->getPackageMappingContainer();
        $packageMappingContainer->assertReviewed();

        foreach ($packageMappingContainer->getPackageMappings() as $packageMapping)
        {
            $packageKey = $packageMapping->getPackageKey();
            if ($this->packageManager->isPackageAvailable($packageKey))
            {
                $this->packageManager->deletePackage($packageKey);
                $this->log('PKG:<comment>%s</comment>: <fg=red>DELETED</fg=red>', [$packageKey]);
            }
            else
            {
                $this->log('PKG:<comment>%s</comment>: <fg=green>not present</fg=green>', [$packageKey]);
            }
        }
    }
}