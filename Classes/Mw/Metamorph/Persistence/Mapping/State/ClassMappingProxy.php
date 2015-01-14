<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;

class ClassMappingProxy extends ClassMapping {

	public function __construct($oldClassName, array $data, MorphConfiguration $configuration) {
		$this->sourceFile   = $configuration->getSourceDirectory() . $data['source'];
		$this->oldClassName = $oldClassName;
		$this->newClassName = $data['newClassname'];
		$this->package      = $data['package'];
		$this->action       = $data['action'];

		if (isset($data['target'])) {
			$this->targetFile = FLOW_PATH_ROOT . 'Packages/Application/' . $data['package'] . '/' . $data['target'];
		}
	}

}