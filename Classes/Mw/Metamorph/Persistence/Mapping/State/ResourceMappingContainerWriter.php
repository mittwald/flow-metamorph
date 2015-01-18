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

class ResourceMappingContainerWriter implements ContainerWriterInterface {

	use YamlStorable;

	public function writeMorphContainer(MorphConfiguration $morphConfiguration) {
		$this->initializeWorkingDirectory($morphConfiguration->getName());

		$resourceMappings = $morphConfiguration->getResourceMappingContainer();
		$data             = ['reviewed' => $resourceMappings->isReviewed(), 'resources' => []];

		foreach ($resourceMappings->getResourceMappings() as $resourceMapping) {
			$mapped = [
				'target'  => $resourceMapping->getTargetFile(),
				'package' => $resourceMapping->getPackage()
			];

			if ($resourceMapping->getTargetFile()) {
				$mapped['target'] = $this->getTargetRelativePath(
					$resourceMapping->getTargetFile(),
					$resourceMapping->getPackage()
				);
			}

			$sourceFile = $this->getSourceRelativePath($resourceMapping->getSourceFile(), $morphConfiguration);

			$data['resources'][$sourceFile] = $mapped;
		}

		if (count($resourceMappings->getResourceMappings())) {
			$this->writeYamlFile('ResourceMap', $data);
			$this->publishConfigurationFileModifiedEvent(
				new MorphConfigurationFileModifiedEvent(
					$morphConfiguration,
					$this->getWorkingFile('ResourceMap.yaml'),
					'Updated resource map.'
				)
			);
		}
	}

}