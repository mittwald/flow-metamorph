<?php
namespace Mw\Metamorph\Transformation\Sorting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Helper class for building the transformation graph.
 *
 * Models a single node with it's edges in the transformation graph.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Sorting
 *
 * @Flow\Scope("prototype")
 */
class TransformationNode {

	const DEFAULT_NAMESPACE = 'Mw\\Metamorph\\Step\\';

	/** @var string */
	private $className;

	/** @var array */
	private $settings = [];

	/** @var TransformationNode[] */
	private $predecessors = [];

	/** @var TransformationNode[] */
	private $successors = [];

	/** @var string */
	private $name;

	public function __construct($name, $className, array $settings = []) {
		if (FALSE === class_exists($className)) {
			$className = static::DEFAULT_NAMESPACE . $className;
		}

		if (FALSE === class_exists($className)) {
			throw new \InvalidArgumentException('Class ' . $className . ' does not exist!');
		}

		$this->name      = $name;
		$this->className = $className;
		$this->settings  = $settings;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	public function getClassName() {
		return $this->className;
	}

	public function getSettings() {
		return $this->settings;
	}

	public function addPredecessor(TransformationNode $predecessor) {
		$hash = spl_object_hash($predecessor);
		if (!isset($this->predecessors[$hash])) {
			$this->predecessors[$hash] = $predecessor;
			$predecessor->addSuccessor($this);
		}
	}

	public function addSuccessor(TransformationNode $successor) {
		$hash = spl_object_hash($successor);
		if (!isset($this->successors[$hash])) {
			$this->successors[$hash] = $successor;
			$successor->addPredecessor($this);
		}
	}

	public function removePredecessor(TransformationNode $predecessor) {
		$hash = spl_object_hash($predecessor);
		if (isset($this->predecessors[$hash])) {
			unset($this->predecessors[$hash]);
		}
	}

	/**
	 * @return TransformationNode[]
	 */
	public function getPredecessors() {
		return array_values($this->predecessors);
	}

	public function getPredecessorCount() {
		return count($this->predecessors);
	}

	/**
	 * @return TransformationNode[]
	 */
	public function getSuccessors() {
		return array_values($this->successors);
	}
}