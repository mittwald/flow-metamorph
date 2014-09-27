<?php
namespace Mw\Metamorph\Domain\Service\Concern;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\MorphCreationData;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;
use TYPO3\Flow\Utility\Files;


/**
 * Class MorphCreationConcern
 */
class MorphCreationConcern
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function create($packageKey, MorphCreationData $data, OutputInterface $out)
    {
        $metaData = new MetaData($packageKey);
        $package  = $this->packageManager->createPackage($packageKey, $metaData);

        $morphData = [
            'sourceDirectory'   => $data->getSourceDirectory(),
            'extensions'        => array_map(
                function ($pattern) { return ['pattern' => $pattern]; },
                $data->getExtensionPatterns()
            ),
            'doctrineMode'      => $data->isKeepingTableStructure() ? 'KEEP_SCHEMA' : 'MIGRATE',
            'pibaseRefactoring' => $data->isAggressivelyRefactoringPiBaseExtensions() ? 'AGGRESSIVE' : 'CAUTIOUS',
        ];

        $configurationPath = $package->getConfigurationPath();
        $morphPath         = Files::concatenatePaths([$configurationPath, 'Metamorph', 'Morph.yml']);

        Files::createDirectoryRecursively(dirname($morphPath));
        file_put_contents($morphPath, Yaml::dump($morphData));
    }


}