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

class ClassMappingContainerWriter {

	use YamlStorable;

	public function writeMorphClassMapping(MorphConfiguration $morphConfiguration) {
		$this->initializeWorkingDirectory($morphConfiguration->getName());

		$classMappings = $morphConfiguration->getClassMappingContainer();
		$data          = ['reviewed' => $classMappings->isReviewed(), 'classes' => []];

		foreach ($classMappings->getClassMappings() as $cls) {
			$mapped = [
				'source'       => $this->getSourceRelativePath($cls->getSourceFile(), $morphConfiguration),
				'newClassname' => $cls->getNewClassName(),
				'package'      => $cls->getPackage(),
				'action'       => $cls->getAction()
			];

			if ($cls->getTargetFile()) {
				$mapped['target'] = $this->getTargetRelativePath($cls->getTargetFile(), $cls->getPackage());
			}

			$data['classes'][$cls->getOldClassName()] = $mapped;
		}

		if (count($classMappings->getClassMappings())) {
			$this->writeYamlFile('ClassMap', $data);
			$this->publishConfigurationFileModifiedEvent(
				new MorphConfigurationFileModifiedEvent(
					$morphConfiguration,
					$this->getWorkingFile('ClassMap.yaml'),
					'Updated class map.'
				)
			);
		}
	}

}