<?php
namespace Mw\Metamorph\Domain\Model\State;

use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 *
 * @Flow\Scope("prototype")
 */
class ResourceMapping {

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $sourceFile;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $targetFile;

	/**
	 * @var string
	 * @Flow\Validate(type="Mw.Metamorph:PackageKey")
	 */
	protected $package;

	public function __construct($sourceFile, $targetFile, $package) {
		$this->package    = $package;
		$this->sourceFile = $sourceFile;
		$this->targetFile = $targetFile;
	}

	/**
	 * @return string
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * @return string
	 */
	public function getSourceFile() {
		return $this->sourceFile;
	}

	/**
	 * @return string
	 */
	public function getTargetFile() {
		return $this->targetFile;
	}

}