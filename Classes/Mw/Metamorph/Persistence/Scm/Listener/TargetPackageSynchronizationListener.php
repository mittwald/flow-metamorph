<?php
namespace Mw\Metamorph\Persistence\Scm\Listener;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\TargetPackageCleanupEvent;
use Mw\Metamorph\Domain\Event\TargetPackageCreatedEvent;
use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Persistence\Scm\Listener
 */
class TargetPackageSynchronizationListener {

	/**
	 * @var \Mw\Metamorph\Persistence\Scm\BackendLocator
	 * @Flow\Inject
	 */
	protected $locator;

	/**
	 * @param TargetPackageCreatedEvent $event
	 * @Event\Listener("Mw\Metamorph\Domain\Event\TargetPackageCreatedEvent", sync=TRUE)
	 */
	public function initializeRepositoryInNewPackages(TargetPackageCreatedEvent $event) {
		$backend = $this->locator->getBackendByConfiguration($event->getMorphConfiguration());
		$backend->initialize($event->getPackage()->getPackagePath(), 'Initialize morph target package.');
	}

	/**
	 * @param TargetPackageCleanupEvent $event
	 * @throws HumanInterventionRequiredException
	 *
	 * @Event\Listener("Mw\Metamorph\Domain\Event\TargetPackageCleanupEvent", sync=TRUE)
	 */
	public function resetRepositoryBeforeExecution(TargetPackageCleanupEvent $event) {
		$backend = $this->locator->getBackendByConfiguration($event->getMorphConfiguration());
		$path    = $event->getPackage()->getPackagePath();

		if ($backend->isModified($path)) {
			throw new HumanInterventionRequiredException(
				"The directory <comment>{$path}</comment> contains changes that " .
				"are not checked in into version control. Please check any " .
				"remaining changes into version control and restart."
			);
		}

		$backend->checkout($path, 'metamorph');
	}

}