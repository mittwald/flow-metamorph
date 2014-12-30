<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Mw\Metamorph\Domain\Model\State\ResourceMapping;

class ResourceMappingProxy extends ResourceMapping {

	public function __construct($sourceFile, array $data) {
		$this->sourceFile = $sourceFile;
		$this->package    = $data['package'];
		$this->targetFile = $data['target'];
	}

}