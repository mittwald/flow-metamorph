<?php
namespace Mw\Metamorph\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * @Flow\Scope("singleton")
 */
class ClassNameValidator extends AbstractValidator {

	protected $acceptsEmptyValues = FALSE;

	protected static $reserved = [
		'include', 'array', 'class'
	];

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param mixed $value
	 * @return void
	 * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException if invalid validation options have been specified in the constructor
	 */
	protected function isValid($value) {
		$components = explode('\\', $value);
		$pattern = '/^[A-Z][a-zA-Z0-9]+$/';

		foreach ($components as $component) {
			if (!preg_match($pattern, $component)) {
				$this->addError("Component \"$component\" of class name $value is no valid namespace or class name.", 1421410370);
			}

			if (in_array(strtolower($component), static::$reserved)) {
				$this->addError("Component \"$component\" of class name $value is a reserved word.", 1421410371);
			}
		}
	}

}