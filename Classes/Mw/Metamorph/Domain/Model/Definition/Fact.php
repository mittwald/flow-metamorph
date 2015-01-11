<?php
namespace Mw\Metamorph\Domain\Model\Definition;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * Interface definition for custom facts.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\Definition
 */
interface Fact {

	/**
	 * Evaluates that fact for a given class definition.
	 *
	 * @param ClassDefinition $classDefinition The class definition for which to evaluate the fact.
	 * @return mixed The fact value.
	 */
	public function evaluate(ClassDefinition $classDefinition);

}