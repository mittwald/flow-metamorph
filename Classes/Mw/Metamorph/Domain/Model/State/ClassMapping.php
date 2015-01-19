<?php
namespace Mw\Metamorph\Domain\Model\State;

use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 *
 * @Flow\Scope("prototype")
 */
class ClassMapping {

	const ACTION_MORPH = 'MORPH';

	const ACTION_IGNORE = 'IGNORE';

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $sourceFile;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $oldClassName;

	/**
	 * @var string
	 * @Flow\Validate(type="Mw.Metamorph:ClassName")
	 */
	protected $newClassName;

	/**
	 * @var string
	 * @Flow\Validate(
	 *   type="Mw.Metamorph:ElementOf",
	 *   options={"set"={ClassMapping::ACTION_MORPH, ClassMapping::ACTION_IGNORE}}
	 * )
	 */
	protected $action = self::ACTION_MORPH;

	/**
	 * @var string
	 * @Flow\Validate(type="Mw.Metamorph:PackageKey")
	 */
	protected $package;

	/**
	 * @var string
	 */
	protected $targetFile;

	public function __construct($sourceFile, $oldClassName, $newClassName, $package) {
		$this->newClassName = $newClassName;
		$this->oldClassName = $oldClassName;
		$this->package      = $package;
		$this->sourceFile   = $sourceFile;
	}

	/**
	 * @param string $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @param mixed $newClassName
	 */
	public function setNewClassName($newClassName) {
		$this->newClassName = $newClassName;
	}

	/**
	 * @return mixed
	 */
	public function getNewClassName() {
		return $this->newClassName;
	}

	/**
	 * @param mixed $oldClassName
	 */
	public function setOldClassName($oldClassName) {
		$this->oldClassName = $oldClassName;
	}

	/**
	 * @return mixed
	 */
	public function getOldClassName() {
		return $this->oldClassName;
	}

	/**
	 * @param mixed $package
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * @return mixed
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * @param mixed $sourceFile
	 */
	public function setSourceFile($sourceFile) {
		$this->sourceFile = $sourceFile;
	}

	/**
	 * @return mixed
	 */
	public function getSourceFile() {
		return $this->sourceFile;
	}

	/**
	 * @param mixed $targetFile
	 */
	public function setTargetFile($targetFile) {
		$this->targetFile = $targetFile;
	}

	/**
	 * @return mixed
	 */
	public function getTargetFile() {
		return $this->targetFile;
	}

}