<?php
namespace Mw\Metamorph\Transformation;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Doctrine\Service as DoctrineService;

class CreateDoctrineMigrations extends AbstractTransformation {

	/**
	 * @var DoctrineService
	 * @Flow\Inject
	 */
	protected $doctrineService;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$this->log('Validating schema.');

		$validationResults = $this->doctrineService->validateMapping();
		if (count($validationResults) === 0) {
			$this->log('Validation <info>passed</info>.');
		} else {
			$this->log('Validation <error>failed</error>');
			$dump = \TYPO3\Flow\var_dump($validationResults, NULL, TRUE);

			throw new HumanInterventionRequiredException(
				'Despite all efforts, your entities could not be cleanly migrated. Please clean up your entities ' .
				'manually, then re-run <comment>morph:execute</comment>.' . "\n\n" . $dump
			);
		}
	}

}