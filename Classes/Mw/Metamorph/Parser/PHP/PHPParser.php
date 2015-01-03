<?php
namespace Mw\Metamorph\Parser\PHP;

use Mw\Metamorph\Parser\ParseError;
use Mw\Metamorph\Parser\ParserInterface;
use PhpParser\Error as PhpParserError;
use PhpParser\Parser;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Parser\PHP
 */
class PHPParser implements ParserInterface {

	/**
	 * @var Parser
	 * @Flow\Inject
	 */
	protected $actualParser;

	public function parseFile($filename) {
		$code = file_get_contents($filename);
		return $this->parseCode($code, $filename);
	}

	public function parseCode($code, $filename = NULL) {
		try {
			return $this->actualParser->parse($code);
		} catch (PhpParserError $error) {
			throw new ParseError($error->getMessage(), $filename);
		}
	}
}