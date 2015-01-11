<?php
namespace Mw\Metamorph\Domain\Model\Definition;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * A special fact that always evaluates to NULL.
 *
 * @package Mw\Metamorph
 * @subpackage Domain\Model\Definition
 */
class NullFact implements Fact {

	public function evaluate(ClassDefinition $classDefinition) {
		return NULL;
	}

}