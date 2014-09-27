<?php
namespace Mw\Metamorph\Domain\Service\Concern;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Annotations as Flow;

class MorphResetConcern
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function reset(MorphConfiguration $configuration, OutputInterface $out)
    {
        $package    = $this->packageManager->getPackage($configuration->getName());
        $workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
        $state      = new MorphExecutionState($workingDir);

        Files::emptyDirectoryRecursively($state->getWorkingDirectory());
    }



}