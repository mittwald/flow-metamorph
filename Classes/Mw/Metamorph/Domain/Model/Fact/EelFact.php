<?php
namespace Mw\Metamorph\Domain\Model\Fact;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\Fact;
use TYPO3\Eel\Context;
use TYPO3\Eel\EelEvaluatorInterface;
use TYPO3\Flow\Annotations as Flow;

/**
 * A custom fact definition that evaluates an EEL expression.
 *
 * @package Mw\Metamorph
 * @subpackage Domain\Model\Fact
 */
class EelFact implements Fact {

	/**
	 * @var EelEvaluatorInterface
	 * @Flow\Inject
	 */
	protected $evaluator;

	public function __construct($expression) {
		$this->expression = str_replace("\n", "", trim($expression));
	}

	public function evaluate(ClassDefinition $classDefinition) {
		$context = new Context(['class' => $classDefinition]);
		return $this->evaluator->evaluate($this->expression, $context);
	}
}