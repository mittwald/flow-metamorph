<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node\Expr\MethodCall;

class MethodPredicate extends Predicate {

	public function onObject() {
		if ($this->node instanceof MethodCall) {
			return new Predicate($this->staticPredicate(TRUE), $this->node->var, $this);
		}
		return new Predicate($this->staticPredicate(FALSE), $this->node, $this);
	}

}