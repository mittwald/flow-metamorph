<?php
namespace Mw\Metamorph\Transformation\Sorting;

class TransformationNode {

	/** @var string */
	private $name;

	/** @var string */
	private $className;

	/** @var array */
	private $settings = [];

	/** @var TransformationNode[] */
	private $predecessors = [];
	private $successors = [];

	public function __construct($name, $className, array $settings = []) {
		if (FALSE === class_exists($className)) {
			$className = 'Mw\\Metamorph\\Transformation\\' . $className;
		}

		if (FALSE === class_exists($className)) {
			throw new \InvalidArgumentException('Class ' . $className . ' does not exist!');
		}

		$this->name      = $name;
		$this->className = $className;
		$this->settings  = $settings;
	}

	public function getClassName() {
		return $this->className;
	}

	public function getSettings() {
		return $this->settings;
	}

	public function addPredecessor(TransformationNode $predecessor) {
		$predecessor->addSuccessor($this);
		$this->predecessors[] = $predecessor;
	}

	public function removePredecessor(TransformationNode $predecessor) {
		$key = array_search($predecessor, $this->predecessors, TRUE);
		if (FALSE !== $key) {
			unset($this->predecessors[$key]);
		}
	}

	/**
	 * @return TransformationNode[]
	 */
	public function getPredecessors() {
		return $this->predecessors;
	}

	public function getPredecessorCount() {
		return count($this->predecessors);
	}

	public function addSuccessor(TransformationNode $successor) {
		$this->successors[] = $successor;
	}

	/**
	 * @return TransformationNode[]
	 */
	public function getSuccessors() {
		return $this->successors;
	}
}