<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ResourceMapping;

class ResourceMappingProxy extends ResourceMapping {

	public function __construct($sourceFile, array $data, MorphConfiguration $configuration) {
		$this->sourceFile = $configuration->getSourceDirectory() . $sourceFile;
		$this->package    = $data['package'];
		$this->targetFile = FLOW_PATH_ROOT . 'Packages/Application/' . $data['package'] . '/' . $data['target'];
	}

}