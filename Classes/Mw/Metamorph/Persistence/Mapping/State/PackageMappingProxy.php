<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Mw\Metamorph\Domain\Model\State\PackageMapping;

class PackageMappingProxy extends PackageMapping {

	public function __construct($extensionKey, array $data) {
		$this->filePath     = $data['path'];
		$this->extensionKey = $extensionKey;
		$this->packageKey   = $data['packageKey'];
		$this->action       = $data['action'];
		$this->description  = $data['description'];
		$this->version      = $data['version'];
		$this->authors      = $data['authors'];
	}

}