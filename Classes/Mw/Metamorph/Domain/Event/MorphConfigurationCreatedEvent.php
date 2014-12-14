<?php
namespace Mw\Metamorph\Domain\Event;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;

class MorphConfigurationCreatedEvent extends AbstractMorphConfigurationEvent {

	/** @var MorphCreationDto */
	private $options;

	public function __construct(MorphConfiguration $morphConfiguration, MorphCreationDto $options) {
		parent::__construct($morphConfiguration);
		$this->options = $options;
	}

	/**
	 * @return MorphCreationDto
	 */
	public function getOptions() {
		return $this->options;
	}

}