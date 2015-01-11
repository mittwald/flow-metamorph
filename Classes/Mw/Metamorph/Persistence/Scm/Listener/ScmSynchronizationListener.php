<?php
namespace Mw\Metamorph\Persistence\Scm\Listener;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\MorphConfigurationCreatedEvent;
use Mw\Metamorph\Domain\Event\MorphConfigurationExecutionStartedEvent;
use Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent;
use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use TYPO3\Flow\Annotations as Flow;

class ScmSynchronizationListener {

	/**
	 * @var \Mw\Metamorph\Persistence\Scm\BackendLocator
	 * @Flow\Inject
	 */
	protected $locator;

	/**
	 * @param MorphConfigurationCreatedEvent $event
	 * @Event\Listener("Mw\Metamorph\Domain\Event\MorphConfigurationCreatedEvent")
	 */
	public function initializeRepositoryListener(MorphConfigurationCreatedEvent $event) {
		$backend   = $this->locator->getBackendByIdentifier($event->getOptions()->getVersionControlSystem());
		$package   = $event->getMorphConfiguration()->getPackage();
		$directory = $package->getPackagePath();

		$backend->initialize($directory);
	}

	/**
	 * @param MorphConfigurationExecutionStartedEvent $event
	 * @throws HumanInterventionRequiredException
	 *
	 * @Event\Listener("Mw\Metamorph\Domain\Event\MorphConfigurationExecutionStartedEvent", async=FALSE)
	 */
	public function ensureConfigurationIsUnmodified(MorphConfigurationExecutionStartedEvent $event) {
		$backend   = $this->locator->getBackendByConfiguration($event->getMorphConfiguration());
		$package   = $event->getMorphConfiguration()->getPackage();
		$directory = $package->getPackagePath();

		if ($backend->isModified($directory)) {
			throw new HumanInterventionRequiredException(
				sprintf(
					'The directory <comment>%s</comment> contains changes that are not checked into version control. ' .
					'Please make sure that a clean working copy exists.',
					$directory
				)
			);
		}
	}

	/**
	 * @param MorphConfigurationFileModifiedEvent $event
	 * @Event\Listener("Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent")
	 */
	public function commitConfigurationChanges(MorphConfigurationFileModifiedEvent $event) {
		$backend   = $this->locator->getBackendByConfiguration($event->getMorphConfiguration());
		$package   = $event->getMorphConfiguration()->getPackage();
		$directory = $package->getPackagePath();

		$backend->commit($directory, $event->getPurpose(), [$event->getRelativeFilename()]);
	}

}