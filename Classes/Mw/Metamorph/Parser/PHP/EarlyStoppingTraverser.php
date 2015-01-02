<?php
namespace Mw\Metamorph\Parser\PHP;

use PhpParser\NodeTraverser;

class EarlyStoppingTraverser extends NodeTraverser {

	public function traverse(array $nodes) {
		foreach ($this->visitors as $visitor) {
			if (NULL !== $return = $visitor->beforeTraverse($nodes)) {
				$nodes = $return;
			}
		}

		try {
			$nodes = $this->traverseArray($nodes);
		} catch (SkipTraversalException $e) {
		} finally {
			foreach ($this->visitors as $visitor) {
				if (NULL !== $return = $visitor->afterTraverse($nodes)) {
					$nodes = $return;
				}
			}
		}

		return $nodes;
	}
}