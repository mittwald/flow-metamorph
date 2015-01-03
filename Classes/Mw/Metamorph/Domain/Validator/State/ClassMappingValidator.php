<?php
namespace Mw\Metamorph\Domain\Validator\State;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for validating class mappings.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Validator\State
 *
 * @Flow\Scope("singleton")
 */
class ClassMappingValidator extends AbstractValidator {

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param mixed $value
	 * @return void
	 * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException if invalid validation options have been specified in the constructor
	 */
	protected function isValid($value) {
		if ($value instanceof ClassMapping) {
			$classname = (new String($value->getNewClassName()));
			$namespace = (new String($value->getPackage()))->replace('.', '\\')->append('\\');

			if (FALSE === $classname->startWith($namespace)) {
				$this->addError(
					"New class name of class {$value->getOldClassName()} must be in namespace {$namespace}.",
					1420301232
				);
			}
		}
	}
}