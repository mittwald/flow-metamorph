<?php
namespace Mw\Metamorph\Domain\Event;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use TYPO3\Flow\Package\PackageInterface;

/**
 * Event that is emitted when a target package is created.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Event
 */
class TargetPackageCreatedEvent extends AbstractMorphConfigurationEvent {

	/**
	 * @var PackageInterface
	 */
	private $package;

	public function __construct(MorphConfiguration $morphConfiguration, PackageInterface $package) {
		parent::__construct($morphConfiguration);
		$this->package = $package;
	}

	/**
	 * @return PackageInterface
	 */
	public function getPackage() {
		return $this->package;
	}

}