<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ResourceMappingContainer;
use TYPO3\Flow\Annotations as Flow;

class ResourceMappingContainerProxy extends ResourceMappingContainer {

	use YamlStorable;

	public function __construct(MorphConfiguration $configuration) {
		$this->configuration = $configuration;
	}

	public function initializeObject() {
		$this->initializeWorkingDirectory($this->configuration->getName());

		$data             = $this->readYamlFile('ResourceMap', FALSE);
		$resourceMappings = [];

		foreach ($this->getArrayProperty($data, 'resources', []) as $sourceFile => $resourceData) {
			$resourceMappings[] = new ResourceMappingProxy($sourceFile, $resourceData, $this->configuration);
		}

		$this->resourceMappings = $resourceMappings;
		$this->reviewed         = $this->getArrayProperty($data, 'reviewed', FALSE);
	}

}