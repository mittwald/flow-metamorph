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
use Mw\Metamorph\Transformation\Sorting\TopologicalTransformationSorter;
use Mw\Metamorph\Transformation\Sorting\TransformationGraphBuilder;
use Mw\Metamorph\Transformation\Sorting\TransformationSorter;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Utility\PositionalArraySorter;

class MorphExecutionConcern {

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * @var TransformationGraphBuilder
	 * @Flow\Inject
	 */
	protected $transformationBuilder;

	/**
	 * @var TransformationSorter
	 * @Flow\Inject
	 */
	protected $transformationSorter;

	public function execute(MorphConfiguration $configuration) {
		$package    = $this->packageManager->getPackage($configuration->getName());
		$workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
		$state      = new MorphExecutionState($workingDir);

		Files::createDirectoryRecursively($state->getWorkingDirectory());

		$nodes = $this->transformationSorter->sort($this->transformationBuilder->build());

		foreach ($nodes as $item) {
			$class = $item->getClassName();

			/** @var \Mw\Metamorph\Transformation\Transformation $transformation */
			$transformation = new $class();
			$transformation->setSettings($item->getSettings());

			$transformation->execute($configuration, $state);
		}
	}

}
