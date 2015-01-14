<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String;

class PropertyAccessPredicate extends Predicate {

	public function propertyIs($key) {
		return $this->buildPredicate(
			function () use ($key) {
				if ($this->node instanceof PropertyFetch) {
					return ($this->node->name instanceof String) && $this->node->name == $key;
				}
				return FALSE;
			}
		);
	}

	public function left() {
		if ($this->node instanceof PropertyFetch) {
			return new Predicate($this->staticPredicate(TRUE), $this->node->var, $this);
		}
		return $this->buildPredicate($this->staticPredicate(FALSE));
	}

}