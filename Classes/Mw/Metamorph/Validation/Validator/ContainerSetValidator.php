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
use TYPO3\Flow\Validation\ValidatorResolver;

/**
 * Validator class for container sets.
 *
 * @package Mw\Metamorph
 * @subpackage Validation\Validator
 */
class ContainerSetValidator extends AbstractValidator {

	/**
	 * @var ValidatorResolver
	 * @Flow\Inject
	 */
	protected $validatorResolver;

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param mixed $value
	 * @return void
	 * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException if invalid validation options have been specified in the constructor
	 */
	protected function isValid($value) {
		foreach ($value as $name => $container) {
			$validator = $this->validatorResolver->getBaseValidatorConjunction(get_class($container));
			$result = $validator->validate($container);

			$this->result->forProperty($name)->merge($result);
		}
	}
}