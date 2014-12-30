<?php
namespace Mw\Metamorph\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\MorphConfigurationCreatedEvent;
use Mw\Metamorph\Domain\Event\MorphConfigurationExecutedEvent;
use Mw\Metamorph\Domain\Event\MorphConfigurationExecutionStartedEvent;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\Concern\MorphCreationConcern;
use Mw\Metamorph\Domain\Service\Concern\MorphExecutionConcern;
use Mw\Metamorph\Domain\Service\Concern\MorphResetConcern;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;
use Mw\Metamorph\Io\DecoratedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;

/**
 * Class MorphService
 *
 * @package Mw\Metamorph\Domain\Service
 * @Flow\Scope("singleton")
 */
class MorphService implements MorphServiceInterface {

	/**
	 * @var MorphCreationConcern
	 * @Flow\Inject
	 */
	protected $creationConcern;

	/**
	 * @var MorphResetConcern
	 * @Flow\Inject
	 */
	protected $resetConcern;

	/**
	 * @var MorphExecutionConcern
	 * @Flow\Inject
	 */
	protected $executionConcern;

	public function reset(MorphConfiguration $configuration) {
		$this->resetConcern->reset($configuration);
	}

	public function create($packageKey, MorphCreationDto $data) {
		$morph = $this->creationConcern->create($packageKey, $data);
		$this->publishCreated(new MorphConfigurationCreatedEvent($morph, $data));
	}

	public function execute(MorphConfiguration $configuration) {
		$this->publishExecutionStart(new MorphConfigurationExecutionStartedEvent($configuration));
		$this->executionConcern->execute($configuration);
		$this->publishExecuted(new MorphConfigurationExecutedEvent($configuration));
	}

	/**
	 * @param MorphConfigurationCreatedEvent $event
	 * @Event\Event
	 */
	protected function publishCreated(MorphConfigurationCreatedEvent $event) { }

	/**
	 * @param MorphConfigurationExecutedEvent $event
	 * @Event\Event
	 */
	protected function publishExecuted(MorphConfigurationExecutedEvent $event) { }

	/**
	 * @param MorphConfigurationExecutionStartedEvent $event
	 * @Event\Event
	 */
	protected function publishExecutionStart(MorphConfigurationExecutionStartedEvent $event) { }

}
