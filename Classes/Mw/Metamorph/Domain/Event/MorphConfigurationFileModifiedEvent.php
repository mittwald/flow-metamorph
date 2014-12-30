<?php
namespace Mw\Metamorph\Domain\Event;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;

class MorphConfigurationFileModifiedEvent extends AbstractMorphConfigurationEvent {

	/**
	 * @var string
	 */
	private $relativeFilename;

	/**
	 * @var string
	 */
	private $purpose;

	public function __construct(MorphConfiguration $configuration, $relativeFilename, $purpose) {
		parent::__construct($configuration);
		$this->relativeFilename = $relativeFilename;
		$this->purpose          = $purpose;
	}

	/**
	 * @return string
	 */
	public function getPurpose() {
		return $this->purpose;
	}

	/**
	 * @return string
	 */
	public function getRelativeFilename() {
		return $this->relativeFilename;
	}

}