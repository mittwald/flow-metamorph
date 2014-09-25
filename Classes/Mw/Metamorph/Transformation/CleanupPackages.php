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
        $packageMap = $state->readYamlFile('PackageMap', TRUE);

        $packageKeys = [];
        foreach ($packageMap['extensions'] as $extensionConfiguration)
        {
            $packageKeys[] = $extensionConfiguration['packageKey'];
        }
        $packageKeys = array_unique($packageKeys);

        foreach ($packageKeys as $packageKey)
        {
            if ($this->packageManager->isPackageAvailable($packageKey))
            {
                $this->packageManager->deletePackage($packageKey);
                $out->writeln(vsprintf('  - PKG:<i>%s</i>: <u>DELETED</u>', [$packageKey]));
            }
            else
            {
                $out->writeln(vsprintf('  - PKG:<i>%s</i>: not present', [$packageKey]));
            }
        }
    }
}