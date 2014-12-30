<?php
namespace Mw\Metamorph\Domain\Validator;

use Helmich\Scalars\Types\String;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * @Flow\Scope("singleton")
 */
class PackageKeyValidator extends AbstractValidator {

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