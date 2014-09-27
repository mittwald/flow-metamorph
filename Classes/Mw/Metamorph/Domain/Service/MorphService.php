<?php
namespace Mw\Metamorph\Domain\Service;



use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\MorphCreationData;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
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



    public function reset(MorphConfiguration $configuration, OutputInterface $out)
    {
        $package    = $this->packageManager->getPackage($configuration->getName());
        $workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
        $state      = new MorphExecutionState($workingDir);

        Files::emptyDirectoryRecursively($state->getWorkingDirectory());
    }



    public function create($packageKey, MorphCreationData $data)
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
        file_put_contents($morphPath, \Symfony\Component\Yaml\Yaml::dump($morphData));
    }



    public function execute(MorphConfiguration $configuration, OutputInterface $out)
    {
        $package    = $this->packageManager->getPackage($configuration->getName());
        $workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
        $state      = new MorphExecutionState($workingDir);

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

            $out->writeln("Executing step <comment>{$name}</comment>.");

            /** @var \Mw\Metamorph\Transformation\Transformation $transformation */
            $transformation = $this->objectManager->get($name);
            $transformation->setSettings(isset($item['settings']) ? $item['settings'] : []);

            try
            {
                $transformation->execute($configuration, $state, $out);
            }
            catch (HumanInterventionRequiredException $exception)
            {
                $out->writeln('');
                $out->writeln('<u><b>Human intervention required</b></u>');
                $out->writeln('');
                $out->writeln($exception->getMessage(), [], 2);
                $out->writeln('');

                return;
            }
        }
    }

}
