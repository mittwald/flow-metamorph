<?php
namespace Mw\Metamorph\Parser\PHP;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Parser\ParseError;
use Mw\Metamorph\Parser\ParserInterface;
use PhpParser\Error as PhpParserError;
use PhpParser\Node;
use PhpParser\Parser;
use TYPO3\Flow\Annotations as Flow;

/**
 * Wrapper class around the PHP Parser.
 *
 * @package    Mw\Metamorph
 * @subpackage Parser\PHP
 * @Flow\Scope("singleton")
 */
class PhpParser implements ParserInterface {

	/**
	 * @var Parser
	 */
	protected $actualParser;

	/**
	 * @param Parser $parser
	 */
	public function __construct(Parser $parser) {
		$this->actualParser = $parser;
	}

	/**
	 * Parses the contents of a file.
	 *
	 * @param string $filename The file path.
	 * @return Node[] A representation of the parsed file content.
	 */
	public function parseFile($filename) {
		$code = file_get_contents($filename);
		return $this->parseCode($code, $filename);
	}

	/**
	 * Parses a string.
	 *
	 * @param string $code     The actual code content.
	 * @param string $filename The original file name.
	 * @return \PhpParser\Node[] A representation of the parsed string.
	 *
	 * @throws ParseError
	 */
	public function parseCode($code, $filename = NULL) {
		try {
			return $this->actualParser->parse($code);
		} catch (PhpParserError $error) {
			throw new ParseError($error->getMessage(), $filename);
		}
	}
}