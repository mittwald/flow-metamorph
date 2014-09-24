<?php
namespace Mw\Metamorph\Domain\Service;



use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\MorphCreationData;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Exception\InvalidConfigurationException;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Flow\Package\MetaData;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Utility\PositionalArraySorter;


/**
 * Class MorphService
 *
 * @package Mw\Metamorph\Domain\Service
 * @Flow\Scope("singleton")
 */
class MorphService
{


    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;


    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;


    /** @var array */
    private $settings;



    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }



    public function create($packageKey, MorphCreationData $data)
    {
        $metaData = new MetaData($packageKey);
        $package  = $this->packageManager->createPackage($packageKey, $metaData, NULL, 'typo3-flow-site');

        $morphData = [
            'sourceDirectory'   => $data->getSourceDirectory(),
            'doctrineMode'      => $data->isKeepingTableStructure() ? 'KEEP_SCHEMA' : 'MIGRATE',
            'pibaseRefactoring' => $data->isAggressivelyRefactoringPiBaseExtensions() ? 'AGGRESSIVE' : 'CAUTIOUS'
        ];

        if ($data->getExtensionPatterns())
        {
            $morphData['extensions'] = $data->getExtensionPatterns();
        }

        $configurationPath = $package->getConfigurationPath();
        $morphPath         = Files::concatenatePaths([$configurationPath, 'Metamorph', 'Morph.yml']);

        Files::createDirectoryRecursively(dirname($morphPath));
        file_put_contents($morphPath, \Symfony\Component\Yaml\Yaml::dump($morphData));
    }



    public function execute(MorphConfiguration $configuration, OutputInterface $out)
    {
        $state = new MorphState(FLOW_PATH_ROOT . 'Build/Metamorph/' . $configuration->getName());

        Files::createDirectoryRecursively($state->getWorkingDirectory());

        $transformationConfig = $this->settings['transformations'];
        $transformationConfig = (new PositionalArraySorter($transformationConfig))->toArray();

        foreach ($transformationConfig as $item)
        {
            $name = $item['name'];
            if (!class_exists($name))
            {
                $name = 'Mw\\Metamorph\\Transformation\\' . $name;
            }

            $out->outputLine("Executing step <i>{$name}</i>.");

            /** @var \Mw\Metamorph\Transformation\Transformation $transformation */
            $transformation = $this->objectManager->get($name);

            try
            {
                $transformation->execute($configuration, $state, $out);
            }
            catch (HumanInterventionRequiredException $exception)
            {
                $out->outputLine();
                $out->outputLine('<u><b>Human intervention required</b></u>');
                $out->outputLine();
                $out->outputFormatted($exception->getMessage(), [], 2);
                $out->outputLine();

                return;
            }
        }
    }

}