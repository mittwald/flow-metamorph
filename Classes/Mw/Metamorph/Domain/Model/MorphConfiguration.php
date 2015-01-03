<?php
namespace Mw\Metamorph\Domain\Model;

use Mw\Metamorph\Domain\Model\Extension\AllMatcher;
use Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Model\State\PackageMappingContainer;
use Mw\Metamorph\Domain\Model\State\ResourceMappingContainer;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageInterface;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model
 *
 * @Flow\Scope("prototype")
 */
class MorphConfiguration {

	const TABLE_STRUCTURE_KEEP    = 'KEEP';
	const TABLE_STRUCTURE_MIGRATE = 'MIGRATE';

	const PIBASE_REFACTOR_CONSERVATIVE = 'CONSERVATIVE';
	const PIBASE_REFACTOR_PROGRESSIVE  = 'PROGRESSIVE';

	/**
	 * @var string
	 * @Flow\Validate(type="Mw.Metamorph:PackageKey")
	 */
	protected $name;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $sourceDirectory;

	/**
	 * @var PackageInterface
	 */
	protected $package;

	/**
	 * @var ExtensionMatcher
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $extensionMatcher;

	/**
	 * What to do with existing database structures.
	 *
	 * @var string
	 * @Flow\Validate(
	 *     type    = "Mw.Metamorph:ElementOf",
	 *     options = {"set" = {MorphConfiguration::TABLE_STRUCTURE_KEEP,
	 *                         MorphConfiguration::TABLE_STRUCTURE_MIGRATE}}
	 * )
	 */
	protected $tableStructureMode = self::TABLE_STRUCTURE_KEEP;

	/**
	 * How aggressively to refactor piBase extensions.
	 *
	 * @var string
	 * @Flow\Validate(
	 *     type    = "Mw.Metamorph:ElementOf",
	 *     options = {"set" = {MorphConfiguration::PIBASE_REFACTOR_CONSERVATIVE,
	 *                         MorphConfiguration::PIBASE_REFACTOR_PROGRESSIVE}}
	 * )
	 */
	protected $pibaseRefactoringMode = self::PIBASE_REFACTOR_CONSERVATIVE;

	/**
	 * @var \Mw\Metamorph\Domain\Model\State\ClassMappingContainer
	 */
	protected $classMappingContainer = NULL;

	/**
	 * @var \Mw\Metamorph\Domain\Model\State\PackageMappingContainer
	 */
	protected $packageMappingContainer = NULL;

	/**
	 * @var \Mw\Metamorph\Domain\Model\State\ResourceMappingContainer
	 */
	protected $resourceMappingContainer = NULL;

	public function __construct($name, $sourceDirectory) {
		$this->name             = $name;
		$this->sourceDirectory  = $sourceDirectory;
		$this->extensionMatcher = new AllMatcher();

		$this->classMappingContainer    = new ClassMappingContainer();
		$this->packageMappingContainer  = new PackageMappingContainer();
		$this->resourceMappingContainer = new ResourceMappingContainer();
	}

	/**
	 * @return string
	 */
	public function getSourceDirectory() {
		return $this->sourceDirectory;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param ExtensionMatcher $extensionMatcher
	 */
	public function setExtensionMatcher(ExtensionMatcher $extensionMatcher) {
		$this->extensionMatcher = $extensionMatcher;
	}

	/**
	 * @return \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher
	 */
	public function getExtensionMatcher() {
		return $this->extensionMatcher;
	}

	/**
	 * @param string $pibaseRefactoringMode
	 */
	public function setPibaseRefactoringMode($pibaseRefactoringMode) {
		$this->pibaseRefactoringMode = $pibaseRefactoringMode;
	}

	/**
	 * @return string
	 */
	public function getPibaseRefactoringMode() {
		return $this->pibaseRefactoringMode;
	}

	/**
	 * @param string $tableStructureMode
	 */
	public function setTableStructureMode($tableStructureMode) {
		$this->tableStructureMode = $tableStructureMode;
	}

	/**
	 * @return string
	 */
	public function getTableStructureMode() {
		return $this->tableStructureMode;
	}

	/**
	 * @return ClassMappingContainer
	 */
	public function getClassMappingContainer() {
		return $this->classMappingContainer;
	}

	/**
	 * @return PackageMappingContainer
	 */
	public function getPackageMappingContainer() {
		return $this->packageMappingContainer;
	}

	/**
	 * @return ResourceMappingContainer
	 */
	public function getResourceMappingContainer() {
		return $this->resourceMappingContainer;
	}

	/**
	 * @param PackageInterface $package
	 */
	public function setPackage(PackageInterface $package) {
		$this->package = $package;
	}

	/**
	 * @return PackageInterface
	 */
	public function getPackage() {
		return $this->package;
	}

	public function __clone() {
		$this->packageMappingContainer  = clone $this->packageMappingContainer;
		$this->classMappingContainer    = clone $this->classMappingContainer;
		$this->resourceMappingContainer = clone $this->resourceMappingContainer;
	}

}