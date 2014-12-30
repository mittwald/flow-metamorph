<?php
namespace Mw\Metamorph\Domain\Model\Definition;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ClassDefinitionContainer {

	private $classDefinitions = [];

	public function add(ClassDefinition $definition) {
		$this->classDefinitions[$definition->getFullyQualifiedName()] = $definition;
	}

	/**
	 * @param string $fullyQualifiedName
	 * @return ClassDefinition
	 */
	public function get($fullyQualifiedName) {
		$fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
		if (array_key_exists($fullyQualifiedName, $this->classDefinitions)) {
			return $this->classDefinitions[$fullyQualifiedName];
		}
		return NULL;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return ClassDefinition[]
	 */
	public function findByFact($name, $value) {
		return $this->findByFilter(
			function (ClassDefinition $class) use ($name, $value) { return $class->getFact($name) == $value; }
		);
	}

	/**
	 * @param callable $filter
	 * @return ClassDefinition[]
	 */
	public function findByFilter(callable $filter) {
		$classes = [];
		foreach ($this->classDefinitions as $classDefinition) {
			if (call_user_func($filter, $classDefinition)) {
				$classes[] = $classDefinition;
			}
		}
		return $classes;
	}
}