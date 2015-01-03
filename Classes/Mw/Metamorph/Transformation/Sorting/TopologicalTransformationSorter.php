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
 * Helper class that sorts a transformation graph using topological sorting.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Sorting
 *
 * @Flow\Scope("singleton")
 */
class TopologicalTransformationSorter implements TransformationSorter {

	/**
	 * Sorts a transformation graph.
	 *
	 * @param TransformationNode[] $nodes The node list to be sorted
	 * @return TransformationNode[] The sorted node list
	 * @throws \Exception When cyclic dependencies are detected
	 */
	public function sort(array $nodes) {
		$sorted = [];

		while (count($nodes) > 0) {
			$next = $this->findNextElementWithNoDependencies($nodes);
			if (NULL === $next) {
				throw new \Exception('Cyclic dependency detected in transformation graph!');
			}

			$sorted[] = $next;

			foreach ($next->getSuccessors() as $successor) {
				$successor->removePredecessor($next);
			}
		}

		return $sorted;
	}

	/**
	 * Extracts one element from the node list that has no predecessors.
	 *
	 * @param TransformationNode[] $nodes The node list
	 * @return TransformationNode One node from the list that has no predecessors
	 */
	private function findNextElementWithNoDependencies(array &$nodes) {
		foreach ($nodes as $key => $node) {
			if ($node->getPredecessorCount() === 0) {
				unset($nodes[$key]);
				return $node;
			}
		}
		return NULL;
	}
}