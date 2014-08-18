<?php
namespace Mw\Metamorph\Domain\Service;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Exception\InvalidConfigurationException;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ResponseInterface;
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


    /** @var array */
    private $settings;



    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }



    public function reset(MorphConfiguration $configuration, OutputInterface $out)
    {
        $state = new MorphState(FLOW_PATH_ROOT . 'Build/Metamorph/' . $configuration->getName());
        Files::emptyDirectoryRecursively($state->getWorkingDirectory());
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
            $transformation->setSettings(isset($item['settings']) ? $item['settings'] : []);

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