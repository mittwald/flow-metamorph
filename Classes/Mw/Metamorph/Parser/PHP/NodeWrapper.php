<?php
namespace Mw\Metamorph\Parser\PHP;

use Mw\Metamorph\Parser\PHP\NQL\Predicate;
use PhpParser\Node;
use TYPO3\Eel\Context;
use TYPO3\Eel\EelEvaluatorInterface;
use TYPO3\Flow\Annotations as Flow;

class NodeWrapper {

	/**
	 * @var Node
	 */
	private $node;

	/**
	 * @var EelEvaluatorInterface
	 * @Flow\Inject
	 */
	protected $evaluator;


	public function __construct(Node $node) {
		$this->node = $node;
	}

	public function e($query){
		$context = new Context($this->q());
		$pred = $this->evaluator->evaluate($query, $context);

		if ($pred instanceof Predicate) {
			return $pred->evaluate();
		}
		return FALSE;
	}

	public function q() {
		return new Predicate(function () { return TRUE; }, $this->node);
	}

	public function node() {
		return $this->node;
	}

}