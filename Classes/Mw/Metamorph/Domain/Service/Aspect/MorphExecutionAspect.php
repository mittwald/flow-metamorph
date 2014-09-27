<?php
namespace Mw\Metamorph\Domain\Service\Aspect;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Io\DecoratedOutputInterface;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Utility\PositionalArraySorter;
use TYPO3\Flow\Annotations as Flow;


class MorphExecutionAspect
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;


    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;


    /**
     * @var array
     * @Flow\Inject(setting="transformations")
     */
    protected $transformations;



    public function execute(MorphConfiguration $configuration, DecoratedOutputInterface $out)
    {
        $package    = $this->packageManager->getPackage($configuration->getName());
        $workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
        $state      = new MorphExecutionState($workingDir);

        Files::createDirectoryRecursively($state->getWorkingDirectory());

        $transformationConfig = (new PositionalArraySorter($this->transformations))->toArray();

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
                $out->writeFormatted($exception->getMessage());
                $out->writeln('');

                return;
            }
        }
    }


} 