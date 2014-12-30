<?php
namespace Mw\Metamorph\Domain\Event;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;

abstract class AbstractMorphConfigurationEvent {

	/** @var MorphConfiguration */
	private $morphConfiguration;

	/**
	 * @param MorphConfiguration $morphConfiguration
	 */
	public function __construct(MorphConfiguration $morphConfiguration) {
		$this->morphConfiguration = $morphConfiguration;
	}

	/**
	 * @return MorphConfiguration
	 */
	public function getMorphConfiguration() {
		return $this->morphConfiguration;
	}

}