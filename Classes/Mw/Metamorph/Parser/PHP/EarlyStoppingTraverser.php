<?php
namespace Mw\Metamorph\Parser\PHP;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use PhpParser\Node;
use PhpParser\NodeTraverser;

/**
 * A special node traverser, supporting early stopping.
 *
 * In this traverser, visitors can cause an early stopping of traversal by
 * throwing an `Mw\Metamorph\Parser\PHP\SkipTraversalException`.
 *
 * @package    Mw\Metamorph
 * @subpackage Parser\PHP
 */
class EarlyStoppingTraverser extends NodeTraverser {

	/**
	 * @param Node[] $nodes
	 * @return Node[]
	 */
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