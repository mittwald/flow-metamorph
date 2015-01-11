<?php
namespace Mw\Metamorph\Transformation\TransformationVisitor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

/**
 * Special node visitor for traversing action controller definitions only.
 *
 * @package Mw\Metamorph
 * @subpackage Transformation\RewriteNodeVisitors
 */
abstract class AbstractControllerVisitor extends AbstractVisitor {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitions;

	/**
	 * @var bool
	 */
	private $isInController = FALSE;

	/**
	 * Called when the visitor enters _any_ node.
	 *
	 * @param Node $node The node replacement
	 * @return void
	 */
	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$name  = $node->namespacedName->toString();
			$class = $this->classDefinitions->get($name);

			if (NULL === $class) {
				return NULL;
			}

			$isController =
				$class->doesInherit('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController') ||
				$class->doesInherit('Tx_Extbase_Mvc_Controller_ActionController');

			$this->isInController = $isController;
		}
	}

	/**
	 * Called when the visitor leaves _any_ node.
	 *
	 * @param Node $node The node to process
	 * @return array|null|Node The node replacement
	 */
	public function leaveNode(Node $node) {
		if (FALSE === $this->isInController) {
			return NULL;
		}

		if ($node instanceof Node\Stmt\Class_) {
			$this->isInController = FALSE;
		}

		return $this->leaveControllerNode($node);
	}

	/**
	 * Called when the visitor leaves a node inside an ActionController.
	 *
	 * @param Node $node The node to process
	 * @return array|null|Node The node replacement
	 */
	abstract protected function leaveControllerNode(Node $node);

}