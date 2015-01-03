<?php
namespace Mw\Metamorph\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Exception;

/**
 * Models a parsing error that occurred while parsing a file.
 *
 * @package    Mw\Metamorph
 * @subpackage Parser
 */
class ParseError extends \Exception {

	protected $filename;

	public function __construct($message, $filename) {
		parent::__construct($message . ' in ' . $filename);
	}

	public function getFilename() {
		return $this->filename;
	}

}