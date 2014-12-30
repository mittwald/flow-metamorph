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

	public function hasClassMapping($oldClassName) {
		return NULL !== $this->getClassMapping($oldClassName);
	}

	public function addClassMapping(ClassMapping $classMapping) {
		if (FALSE === $this->hasClassMapping($classMapping->getOldClassName())) {
			$this->reviewed        = FALSE;
			$this->classMappings[] = $classMapping;
		}
	}

	public function getClassMapping($oldClassName) {
		return $this->getClassMappingByFilter(
			function (ClassMapping $mapping) use ($oldClassName) {
				return $mapping->getOldClassName() === $oldClassName;
			}
		);
	}

	public function getClassMappingByNewClassName($newClassName) {
		return $this->getClassMappingByFilter(
			function (ClassMapping $mapping) use ($newClassName) {
				return $mapping->getNewClassName() === $newClassName;
			}
		);
	}

	public function getClassMappingByFilter(callable $filter) {
		foreach ($this->classMappings as $classMapping) {
			if (call_user_func($filter, $classMapping)) {
				return $classMapping;
			}
		}
		return NULL;
	}

}