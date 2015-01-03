<?php
namespace Mw\Metamorph\Parser\PHP;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * Exception that can be thrown by visitors using the `EarlyStoppingTraverser`
 *
 * @package    Mw\Metamorph
 * @subpackage Parser\PHP
 */
class SkipTraversalException extends \Exception {

}