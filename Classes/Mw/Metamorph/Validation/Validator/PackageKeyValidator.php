<?php
namespace Mw\Metamorph\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Helmich\Scalars\Types\String;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * @Flow\Scope("singleton")
 */
class PackageKeyValidator extends AbstractValidator {

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
		$hasVendorNamespace = (new String($value))->split('.')->length() >= 2;
		if (FALSE === $hasVendorNamespace) {
			$this->addError(
				'"' . $value . '" is not a valid package key, because there is no vendor namespace!',
				1416158223
			);
		}
	}
}