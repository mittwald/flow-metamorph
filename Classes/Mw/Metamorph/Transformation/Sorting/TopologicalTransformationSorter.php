<?php
namespace Mw\Metamorph\Transformation\Sorting;

class TopologicalTransformationSorter implements TransformationSorter {

	/**
	 * @param array $nodes
	 * @return TransformationNode[]
	 * @throws \Exception
	 */
	public function sort(array $nodes) {
		$sorted = [];

		while (count($nodes) > 0) {
			$next = $this->findNextElementWithNoDependencies($nodes);
			if (NULL === $next) {
				throw new \Exception('Cyclic dependency detected in transformation graph!');
			}

			$sorted[] = $next;

			foreach($next->getSuccessors() as $successor) {
				$successor->removePredecessor($next);
			}
		}

		return $sorted;
	}

	/**
	 * @param TransformationNode[] $nodes
	 * @return TransformationNode
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