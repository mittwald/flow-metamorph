<?php
namespace Mw\Metamorph\Step;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\TargetPackageResynchronizeEvent;
use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\AbstractTransformation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface;

class ResynchronizePackages extends AbstractTransformation {

	/**
	 * @var PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$packages = $configuration
			->getPackageMappingContainer()
			->getPackageMappingsByAction(PackageMapping::ACTION_MORPH);

		$errors = [];
		foreach ($packages as $packageMapping) {
			$package = $this->packageManager->getPackage($packageMapping->getPackageKey());
			try {
				$this->emitPackageResyncEvent(new TargetPackageResynchronizeEvent($configuration, $package));
				$this->log("Synchronizing <comment>{$packageMapping->getPackageKey()}</comment>: <info>success</info>");
			} catch (HumanInterventionRequiredException $error) {
				$this->log("Synchronizing <comment>{$packageMapping->getPackageKey()}</comment>: <error>error</error>");
				$errors[$packageMapping->getPackageKey()] = $error;
			}
		}

		if (count($errors) > 0) {
			$errorDescription = '';

			foreach ($errors as $packageKey => $error) {
				$errorDescription .= "<comment>{$packageKey}</comment>:\n  {$error->getMessage()}\n";
			}

			throw new HumanInterventionRequiredException(trim($errorDescription));
		}
	}

	/**
	 * @param TargetPackageResynchronizeEvent $event
	 * @Event\Event
	 */
	protected function emitPackageResyncEvent(TargetPackageResynchronizeEvent $event) { }
}