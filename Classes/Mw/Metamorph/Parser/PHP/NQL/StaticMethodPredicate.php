<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node\Expr\StaticCall;

class StaticMethodPredicate extends Predicate {

	public function onClass() {
		if ($this->node instanceof StaticCall) {
			return new Predicate($this->staticPredicate(TRUE), $this->node->class, $this);
		}
		return new Predicate($this->staticPredicate(FALSE), $this->node, $this);
	}
}