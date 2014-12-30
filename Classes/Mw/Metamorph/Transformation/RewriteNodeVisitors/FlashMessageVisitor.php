<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;

use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class FlashMessageVisitor extends AbstractControllerVisitor {

	public function leaveControllerNode(Node $node) {
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