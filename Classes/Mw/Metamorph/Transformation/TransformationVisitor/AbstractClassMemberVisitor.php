<?php
namespace Mw\Metamorph\Transformation\TransformationVisitor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Parser\PHP\NodeWrapper;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

/**
 * Special node visitor for traversing nodes within class definitions only.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\RewriteNodeVisitors
 */
abstract class AbstractClassMemberVisitor extends AbstractVisitor {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitions;

	/**
	 * @var ClassDefinition
	 */
	protected $currentClass;

	/**
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Called when the visitor enters _any_ node.
	 *
	 * @param Node $node The node replacement
	 * @return null|Node
	 */
	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$name  = $node->namespacedName->toString();
			$class = $this->classDefinitions->get($name);

			if (NULL === $class) {
				return NULL;
			}

			$this->currentClass = $class;
		} else if (NULL !== $this->currentClass) {
			$wrapper = new NodeWrapper($node);
			if ($this->applyFilters($wrapper)) {
				$return = $this->enterClassMemberNode($wrapper);
				if ($return instanceof NodeWrapper) {
					$return = $return->node();
				}
				return $return;
			}
		}

		return NULL;
	}

	/**
	 * Called when the visitor enters a node inside a class.
	 *
	 * @param NodeWrapper $node The node to process
	 * @return array|null|Node The node replacement
	 */
	protected function enterClassMemberNode(NodeWrapper $node) { }

	/**
	 * Called when the visitor leaves _any_ node.
	 *
	 * @param Node $node The node to process
	 * @return array|null|Node The node replacement
	 */
	public function leaveNode(Node $node) {
		if (NULL === $this->currentClass) {
			return NULL;
		}

		if ($node instanceof Node\Stmt\Class_) {
			$this->currentClass = NULL;
		}

		$wrapper = new NodeWrapper($node);
		if ($this->applyFilters($wrapper)) {
			$return = $this->leaveClassMemberNode($wrapper);
			if ($return instanceof NodeWrapper) {
				$return = $return->node();
			}
			return $return;
		}
		return FALSE;
	}

	/**
	 * Called when the visitor leaves a node inside a class.
	 *
	 * @param NodeWrapper $node The node to process
	 * @return array|null|Node The node replacement
	 */
	protected function leaveClassMemberNode(NodeWrapper $node) { }

	private function applyFilters(NodeWrapper $node) {
		if (count($this->filters) === 0) {
			return TRUE;
		} else {
			foreach ($this->filters as $filter) {
				if ($node->e($filter)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

}