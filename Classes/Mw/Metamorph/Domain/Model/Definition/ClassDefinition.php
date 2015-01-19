<?php
namespace Mw\Metamorph\Domain\Model\Definition;

use Mw\Metamorph\Domain\Model\State\ClassMapping;
use TYPO3\Flow\Annotations as Flow;

class ClassDefinition {

	/**
	 * @var FactContainer
	 * @Flow\Inject
	 */
	protected $factContainer;

	private $name;

	private $namespace;

	/** @var ClassDefinition */
	private $parentClass = NULL;

	/** @var ClassDefinition[] */
	private $interfaces = [];

	/** @var array */
	private $facts = [];

	/** @var PropertyDefinition[] */
	private $properties = [];

	/** @var ClassMapping */
	private $classMapping;

	static public function fromFqdn($fqdn) {
		$parts = explode('\\', $fqdn);
		$class = array_pop($parts);

		return new ClassDefinition($class, implode('\\', $parts));
	}

	public function __construct($name, $namespace) {
		$this->name      = $name;
		$this->namespace = $namespace;
	}

	public function addInterface(ClassDefinition $interface) {
		$this->interfaces[] = $interface;
	}

	public function doesImplement($fullyQualifiedName) {
		foreach ($this->getInterfaces() as $interface) {
			if ($fullyQualifiedName === $interface->getFullyQualifiedName()) {
				return TRUE;
			}
			if ($interface->doesInherit($fullyQualifiedName)) {
				return TRUE;
			}
		}

		if (NULL !== $this->getParentClass() && $this->getParentClass()->doesImplement($fullyQualifiedName)) {
			return TRUE;
		}

		return FALSE;
	}

	public function getInterfaces() {
		return $this->interfaces;
	}

	public function getFullyQualifiedName() {
		return ltrim($this->namespace . '\\' . $this->name, '\\');
	}

	public function getRelativeName() {
		return $this->name;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function doesInherit($fullyQualifiedName) {
		if ($this->getParentClass() !== NULL) {
			if ($fullyQualifiedName == $this->getParentClass()->getFullyQualifiedName()) {
				return TRUE;
			} else {
				return $this->getParentClass()->doesInherit($fullyQualifiedName);
			}
		}
		return FALSE;
	}

	public function getParentClass() {
		return $this->parentClass;
	}

	public function setParentClass(ClassDefinition $parentClass) {
		$this->parentClass = $parentClass;
	}

	/**
	 * Gets a custom fact about this class.
	 *
	 * If this fact is not directly known for this class, the fact will be
	 * looked up in the FactContainer and the evaluated for this class.
	 *
	 * @param string $name The fact name.
	 * @return mixed The fact value. Might really be anything.
	 */
	public function getFact($name) {
		if (array_key_exists($name, $this->facts)) {
			return $this->facts[$name];
		}

		return $this->factContainer->getFact($name)->evaluate($this);
	}

	/**
	 * Sets a custom fact about this class.
	 *
	 * @param string $name The fact name.
	 * @param mixed  $fact The fact value. Can be anything.
	 * @return void
	 */
	public function setFact($name, $fact) {
		$this->facts[$name] = $fact;
	}

	/**
	 * @return ClassMapping
	 */
	public function getClassMapping() {
		return $this->classMapping;
	}

	/**
	 * @param ClassMapping $classMapping
	 */
	public function setClassMapping(ClassMapping $classMapping) {
		$this->classMapping = $classMapping;
	}

	/**
	 * @param PropertyDefinition $propertyDefinition
	 * @return void
	 */
	public function addProperty(PropertyDefinition $propertyDefinition) {
		$this->properties[$propertyDefinition->getName()] = $propertyDefinition;
	}

	/**
	 * @param string $name
	 * @return PropertyDefinition
	 */
	public function getProperty($name) {
		return $this->hasProperty($name) ? $this->properties[$name] : NULL;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasProperty($name) {
		return array_key_exists("$name", $this->properties);
	}

}