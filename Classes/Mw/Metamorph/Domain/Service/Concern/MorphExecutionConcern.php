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
	 * @var array
	 * @Flow\Inject(setting="transformations")
	 */
	protected $transformations;

	public function execute(MorphConfiguration $configuration) {
		$package    = $this->packageManager->getPackage($configuration->getName());
		$workingDir = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
		$state      = new MorphExecutionState($workingDir);

		Files::createDirectoryRecursively($state->getWorkingDirectory());

		$transformationConfig = (new PositionalArraySorter($this->transformations))->toArray();

		foreach ($transformationConfig as $item) {
			$name = $item['name'];
			if (!class_exists($name)) {
				$name = 'Mw\\Metamorph\\Transformation\\' . $name;
			}

			/** @var \Mw\Metamorph\Transformation\Transformation $transformation */
			$transformation = new $name();
			$transformation->setSettings(isset($item['settings']) ? $item['settings'] : []);

			$transformation->execute($configuration, $state);
		}
	}

}
