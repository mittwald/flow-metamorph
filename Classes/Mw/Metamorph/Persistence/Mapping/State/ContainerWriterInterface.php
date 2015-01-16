<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;

/**
 * Interface definition for configuration writers.
 *
 * @package    Mw\Metamorph
 * @subpackage Persistence\Mapping\State
 */
interface ContainerWriterInterface {

	public function writeMorphContainer(MorphConfiguration $morphConfiguration);

} 