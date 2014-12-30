<?php
namespace Mw\Metamorph\Domain\Service;

class MorphExecutionState {

	private $workingDirectory;

	public function __construct($workingDirectory) {
		$this->workingDirectory = $workingDirectory;
	}

	/**
	 * @return string
	 */
	public function getWorkingDirectory() {
		return $this->workingDirectory;
	}

}