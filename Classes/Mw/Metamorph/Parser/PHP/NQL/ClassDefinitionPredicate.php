<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node\Stmt\Class_;

class ClassDefinitionPredicate extends Predicate {

	public function inherits($className) {
		return $this->buildPredicate(
			function () use ($className) {
				if ($this->node instanceof Class_) {
					return $this->node->extends && $this->node->extends->toString() == $className;
				}
				return FALSE;
			}
		);
	}

} 