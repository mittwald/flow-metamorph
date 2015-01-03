<?php
namespace Mw\Metamorph\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * General interface for different kinds of parsers.
 *
 * @package    Mw\Metamorph
 * @subpackage Parser
 */
interface ParserInterface {

	/**
	 * Parses the contents of a file.
	 *
	 * @param string $filename The file path.
	 * @return mixed A representation of the parsed file content.
	 */
	public function parseFile($filename);

	/**
	 * Parses a string.
	 *
	 * @param string $code The actual code content.
	 * @return mixed A representation of the parsed string.
	 */
	public function parseCode($code);

}