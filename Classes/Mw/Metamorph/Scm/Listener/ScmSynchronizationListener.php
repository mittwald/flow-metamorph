<?php
namespace Mw\Metamorph\Scm\Listener;


use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\MorphConfigurationCreatedEvent;
use Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent;
use Mw\Metamorph\Scm\BackendLocator;
use TYPO3\Flow\Annotations as Flow;


class ScmSynchronizationListener
{



    /**
     * @var BackendLocator
     * @Flow\Inject
     */
    protected $locator;



    /**
     * @param MorphConfigurationCreatedEvent $event
     * @Event\Listener("Mw\Metamorph\Domain\Event\MorphConfigurationCreatedEvent")
     */
    public function initializeRepositoryListener(MorphConfigurationCreatedEvent $event)
    {
        $backend   = $this->locator->getBackendByIdentifier($event->getOptions()->getVersionControlSystem());
        $package   = $event->getMorphConfiguration()->getPackage();
        $directory = $package->getPackagePath();

        $backend->initialize($directory);
    }



    /**
     * @param MorphConfigurationFileModifiedEvent $event
     * @Event\Listener("Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent")
     */
    public function commitConfigurationChanges(MorphConfigurationFileModifiedEvent $event)
    {
        $backend   = $this->locator->getBackendByConfiguration($event->getMorphConfiguration());
        $package   = $event->getMorphConfiguration()->getPackage();
        $directory = $package->getPackagePath();

        $backend->commit($directory, $event->getPurpose(), [$event->getRelativeFilename()]);
    }

}