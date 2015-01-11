<?php
namespace Mw\Metamorph\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for validating action values.
 *
 * @package    Mw\Metamorph
 * @subpackage Validation\Validator
 *
 * @Flow\Scope("prototype")
 */
class ElementOfValidator extends AbstractValidator {

	/**
	 * @var array
	 */
	protected $supportedOptions = array(
		'set' => array([], 'Known elements', 'array'),
	);

	protected $acceptsEmptyValues = FALSE;

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param mixed $value
	 * @return void
	 * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException if invalid validation options have been specified in the constructor
	 */
	protected function isValid($value) {
		if (count($this->options['set']) === 0) {
			throw new InvalidValidationOptionsException('"set" must have at least one value!');
		}

		if (FALSE === in_array($value, $this->options['set'])) {
			$this->addError(
				"'{$value}' is not a valid value. Must be one of the following: "
				. implode(', ', $this->options['set']),
				1420300462
			);
		}
	}
}