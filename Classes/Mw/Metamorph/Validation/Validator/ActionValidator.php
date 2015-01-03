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
class ActionValidator extends AbstractValidator {

	/**
	 * @var array
	 */
	protected $supportedOptions = array(
		'actions' => array([], 'Known actions', 'array'),
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
		if (count($this->options['actions']) === 0) {
			throw new InvalidValidationOptionsException('"actions" must have at least one value!');
		}

		if (FALSE === in_array($value, $this->options['actions'])) {
			$this->addError(
				"'{$value}' is not a valid action. Must be one of the following: "
				. implode(', ', $this->options['actions']),
				1420300462
			);
		}
	}
}