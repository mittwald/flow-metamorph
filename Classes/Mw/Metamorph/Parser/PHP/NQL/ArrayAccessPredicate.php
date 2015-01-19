<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String;

class ArrayAccessPredicate extends Predicate {

	public function keyIs($key) {
		return $this->buildPredicate(
			function () use ($key) {
				if ($this->node instanceof ArrayDimFetch) {
					if (is_string($key)) {
						return ($this->node->dim instanceof String) && $this->node->dim == $key;
					} elseif (is_numeric($key)) {
						return ($this->node->dim instanceof LNumber) && $this->node->dim == $key;
					}
				}
				return FALSE;
			}
		);
	}

	public function left() {
		if ($this->node instanceof ArrayDimFetch) {
			return new Predicate($this->staticPredicate(TRUE), $this->node->var, $this);
		}
		return $this->buildPredicate($this->staticPredicate(FALSE));
	}

}