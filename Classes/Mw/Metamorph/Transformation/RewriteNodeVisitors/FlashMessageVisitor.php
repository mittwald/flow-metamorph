<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class FlashMessageVisitor extends AbstractVisitor {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitions;

	private $isInController = FALSE;

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

	public function leaveNode(Node $node) {
		if (FALSE === $this->isInController) {
			return NULL;
		}

		if ($node instanceof Node\Stmt\Class_) {
			$this->isInController = FALSE;
		}

		if ($node instanceof Node\Expr\MethodCall) {
			$var  = $node->var;
			$name = $node->name;

			$isFlashMessageAdd =
				($var instanceof Node\Expr\PropertyFetch) &&
				($var->name == 'flashMessages') &&
				($name == 'add');

			if ($isFlashMessageAdd) {
				return new Node\Expr\MethodCall(
					new Node\Expr\Variable('this'),
					'addFlashMessage',
					[$node->args[0]]
				);
			}
		}

		return NULL;
	}

}