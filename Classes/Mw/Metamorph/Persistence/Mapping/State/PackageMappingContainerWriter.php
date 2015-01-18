<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent;
use Mw\Metamorph\Domain\Model\MorphConfiguration;

class PackageMappingContainerWriter implements ContainerWriterInterface {

	use YamlStorable;

	public function writeMorphContainer(MorphConfiguration $morphConfiguration) {
		$this->initializeWorkingDirectory($morphConfiguration->getName());

		$packageMappings = $morphConfiguration->getPackageMappingContainer();
		$data            = ['reviewed' => $packageMappings->isReviewed(), 'extensions' => []];

		foreach ($packageMappings->getPackageMappings() as $packageMapping) {
			$data['extensions'][$packageMapping->getExtensionKey()] = [
				'path'        => $this->getSourceRelativePath($packageMapping->getFilePath(), $morphConfiguration),
				'packageKey'  => $packageMapping->getPackageKey(),
				'action'      => $packageMapping->getAction(),
				'description' => $packageMapping->getDescription(),
				'version'     => $packageMapping->getVersion(),
				'authors'     => $packageMapping->getAuthors()
			];
		}

		if (count($packageMappings->getPackageMappings())) {
			$this->writeYamlFile('PackageMap', $data);
			$this->publishConfigurationFileModifiedEvent(
				new MorphConfigurationFileModifiedEvent(
					$morphConfiguration,
					$this->getWorkingFile('PackageMap.yaml'),
					'Updated package map.'
				)
			);
		}
	}
}