<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Flow\Annotations as Flow;



class CreatePackages
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $packageMap  = $state->readYamlFile('PackageMap', TRUE);
        $packageKeys = [];
        foreach ($packageMap['extensions'] as $extensionConfiguration)
        {
            $packageKeys[] = $extensionConfiguration['packageKey'];
        }
        $packageKeys = array_unique($packageKeys);

        foreach ($packageKeys as $packageKey)
        {
            $this->packageManager->createPackage($packageKey, NULL, NULL, 'typo3-flow-package');
            $out->outputLine('  - Created package <i>%s</i>', [$packageKey]);
        }
    }
} 