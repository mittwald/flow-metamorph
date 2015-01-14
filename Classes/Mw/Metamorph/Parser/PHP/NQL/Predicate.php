<?php
namespace Mw\Metamorph\Parser\PHP\NQL;

use PhpParser\Node;

class Predicate {

	/**
	 * @var callable
	 */
	protected $predicate;

	/**
	 * @var Predicate
	 */
	protected $previous;

	/**
	 * @var Node
	 */
	protected $node;

	public function __construct(callable $predicate, Node $node, Predicate $previous = NULL) {
		$this->predicate = $predicate;
		$this->previous  = $previous;
		$this->node      = $node;
	}

	public function is($cls) {
		return new Predicate(
			function () use ($cls) { return is_a($this->node, $cls); },
			$this->node,
			$this
		);
	}

	public function isMethodCall($name = NULL) {
		return new MethodPredicate(
			function () use ($name) {
				if ($this->node instanceof Node\Expr\MethodCall) {
					return ($name && $this->node->name === $name) || ($name === NULL);
				}
				return FALSE;
			},
			$this->node,
			$this
		);
	}

	public function isStaticMethodCall($name = NULL) {
		return new StaticMethodPredicate(
			function () use ($name) {
				if ($this->node instanceof Node\Expr\StaticCall) {
					return ($name && $this->node->name === $name) || ($name === NULL);
				}
				return FALSE;
			},
			$this->node,
			$this
		);
	}

	public function isName($constraint = NULL) {
		return $this->buildPredicate(
			function () use ($constraint) {
				if (!$this->node instanceof Node\Name) {
					return FALSE;
				}

				if (is_string($constraint)) {
					return $this->node->toString() == $constraint;
				} elseif (is_callable($constraint)) {
					return call_user_func($constraint, $this->node->toString());
				}
				return TRUE;
			}
		);
	}

	public function isVariable($name) {
		return $this->buildPredicate(
			function () use ($name) {
				return $this->node instanceof Node\Expr\Variable && $this->node->name == $name;
			}
		);
	}

	public function isArrayAccess() {
		return new ArrayAccessPredicate(
			function() {
				return $this->is(Node\Expr\ArrayDimFetch::class);
			},
			$this->node,
			$this
		);
	}
	public function isPropertyAccess() {
		return new PropertyAccessPredicate(
			function() {
				return $this->is(Node\Expr\PropertyFetch::class);
			},
			$this->node,
			$this
		);
	}

	public function isGlobalsAccess($name) {
		$array = $this->isArrayAccess();
		return $array->keyIs($name)->_and($array->left()->isVariable('GLOBALS'));
	}

	public function evaluate() {
		$result = $this->previous ? $this->previous->evaluate() : TRUE;
		return $result && call_user_func_array($this->predicate, []);
	}

	public function _and(Predicate $pred) {
		return new Predicate($this->staticPredicate(TRUE), $this->node, $pred);
	}

	protected function buildPredicate(callable $pred) {
		return new Predicate($pred, $this->node, $this);
	}

	protected function staticPredicate($var) {
		return function () use ($var) { return $var; };
	}

}