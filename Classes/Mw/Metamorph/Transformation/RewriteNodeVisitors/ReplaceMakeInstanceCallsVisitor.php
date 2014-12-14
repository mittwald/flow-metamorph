<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;

use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class ReplaceMakeInstanceCallsVisitor extends AbstractVisitor {

	/**
	 * @var Node\Stmt\Class_
	 */
	private $currentClass;

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$this->currentClass = $node;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Expr\StaticCall) {
			if ($node->class instanceof Node\Name\FullyQualified &&
				$this->isGeneralUtilityClass($node->class) &&
				$node->name === 'makeInstance'
			) {
				$args = $node->args;

				$className = array_shift($args)->value;
				if ($className instanceof Node\Scalar\String) {
					$className = new Node\Name\FullyQualified($className->value);
				}

				return new Node\Expr\New_($className, $args);
			}
		}
		return NULL;
	}

	private function isGeneralUtilityClass(Node\Name $name) {
		return (
			$name == 'Mw\\T3Compat\\Utility\\GeneralUtility' ||
			$name == 't3lib_div' ||
			$name == 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility'
		);
	}

}