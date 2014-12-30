<?php
namespace Mw\Metamorph\Transformation\Task\Builder;

use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Task\AddPropertyToClassTask;

class AddPropertyToClassTaskBuilder {

	/** @var string */
	private $targetClassName;

	/** @var string */
	private $propertyName;

	/** @var string */
	private $propertyType;

	/** @var string */
	private $propertyIsPublic;

	/** @var array */
	private $propertyAnnotations = [];

	/**
	 * @param string|AnnotationRenderer $propertyAnnotation
	 * @return self
	 */
	public function addAnnotation($propertyAnnotation) {
		if ($propertyAnnotation instanceof AnnotationRenderer) {
			$propertyAnnotation = $propertyAnnotation->render();
		}

		$this->propertyAnnotations[] = $propertyAnnotation;
		return $this;
	}

	/**
	 * @return self
	 */
	public function setPublic() {
		$this->propertyIsPublic = TRUE;
		return $this;
	}

	/**
	 * @return self
	 */
	public function setProtected() {
		$this->propertyIsPublic = FALSE;
		return $this;
	}

	/**
	 * @param string $propertyName
	 * @return self
	 */
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
		return $this;
	}

	/**
	 * @param string $propertyType
	 * @return self
	 */
	public function setPropertyType($propertyType) {
		$this->propertyType = $propertyType;
		return $this;
	}

	/**
	 * @param string $targetClassName
	 * @return self
	 */
	public function setTargetClassName($targetClassName) {
		$this->targetClassName = $targetClassName;
		return $this;
	}

	public function buildTask() {
		return new AddPropertyToClassTask(
			$this->targetClassName,
			$this->propertyName,
			$this->propertyType,
			$this->propertyIsPublic,
			$this->propertyAnnotations
		);
	}

}