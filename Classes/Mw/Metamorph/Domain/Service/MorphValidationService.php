<?php
namespace Mw\Metamorph\Domain\Service;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\ValidatorResolver;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Service
 *
 * @Flow\Scope("singleton")
 */
class MorphValidationService {

	/**
	 * @var ValidatorResolver
	 * @Flow\Inject
	 */
	protected $validationResolver;

	/**
	 * @param MorphConfiguration $configuration
	 * @param array              $validationGroups
	 * @return \TYPO3\Flow\Error\Result
	 */
	public function validate(MorphConfiguration $configuration, $validationGroups = ['Default']) {
		$validator = $this->validationResolver->getBaseValidatorConjunction(
			'Mw\\Metamorph\\Domain\\Model\\MorphConfiguration',
			$validationGroups
		);

		return $validator->validate(clone $configuration);
	}

}