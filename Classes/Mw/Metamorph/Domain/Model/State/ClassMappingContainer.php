<?php
namespace Mw\Metamorph\Domain\Model\State;

use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 *
 * @Flow\Scope("prototype")
 */
class ClassMappingContainer {

	use Reviewable;

	/**
	 * @var array<\Mw\Metamorph\Domain\Model\State\ClassMapping>
	 */
	protected $classMappings = [];

	/**
	 * @return ClassMapping[]
	 */
	public function getClassMappings() {
		return $this->classMappings;
	}

	/**
	 * @param string $oldClassName
	 * @return bool
	 */
	public function hasClassMapping($oldClassName) {
		return NULL !== $this->getClassMapping($oldClassName);
	}

	/**
	 * @param ClassMapping $classMapping
	 */
	public function addClassMapping(ClassMapping $classMapping) {
		if (FALSE === $this->hasClassMapping($classMapping->getOldClassName())) {
			$this->reviewed        = FALSE;
			$this->classMappings[] = $classMapping;
		}
	}

	/**
	 * @param string $oldClassName
	 * @return ClassMapping
	 */
	public function getClassMapping($oldClassName) {
		return $this->getClassMappingByFilter(
			function (ClassMapping $mapping) use ($oldClassName) {
				return $mapping->getOldClassName() === $oldClassName;
			}
		);
	}

	/**
	 * @param string $newClassName
	 * @return ClassMapping
	 */
	public function getClassMappingByNewClassName($newClassName) {
		return $this->getClassMappingByFilter(
			function (ClassMapping $mapping) use ($newClassName) {
				return $mapping->getNewClassName() === $newClassName;
			}
		);
	}

	/**
	 * @param callable $filter
	 * @return ClassMapping
	 */
	public function getClassMappingByFilter(callable $filter) {
		foreach ($this->classMappings as $classMapping) {
			if (call_user_func($filter, $classMapping)) {
				return $classMapping;
			}
		}
		return NULL;
	}

	/**
	 * @param callable $filter
	 * @return ClassMapping[]
	 */
	public function getClassMappingsByFilter(callable $filter) {
		$found = [];
		foreach ($this->classMappings as $classMapping) {
			if (call_user_func($filter, $classMapping)) {
				$found[] = $classMapping;
			}
		}
		return $found;
	}

}
