<?php
namespace Mw\Metamorph\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cache\Frontend\FrontendInterface;

/**
 * Decorator for making parsing output cacheable.
 *
 * @package    Mw\Metamorph
 * @subpackage Parser
 */
class CachingDecorator implements ParserInterface {

	/**
	 * @var ParserInterface
	 */
	protected $actualParser;

	/**
	 * @var FrontendInterface
	 * @Flow\Inject
	 */
	protected $cache;

	/**
	 * Creates a new caching decorator.
	 *
	 * @param ParserInterface $actualParser The actual, decorated parser.
	 */
	public function __construct(ParserInterface $actualParser) {
		$this->actualParser = $actualParser;
	}

	/**
	 * Parses the contents of a file.
	 *
	 * @param string $filename The file path.
	 * @return mixed A representation of the parsed file content.
	 */
	public function parseFile($filename) {
		$entryIdentifier = 'AST_ForFile_' . sha1_file($filename);
		if (FALSE === $this->cache->has($entryIdentifier)) {
			$syntaxTree = $this->actualParser->parseFile($filename);
			$this->cache->set($entryIdentifier, $syntaxTree);
		} else {
			$syntaxTree = $this->cache->get($entryIdentifier);
		}
		return $syntaxTree;
	}

	/**
	 * Parses a string.
	 *
	 * @param string $code The actual code content.
	 * @return mixed A representation of the parsed string.
	 */
	public function parseCode($code) {
		$entryIdentifier = 'AST_ForString_' . sha1($code);
		if (FALSE === $this->cache->has($entryIdentifier)) {
			$syntaxTree = $this->actualParser->parseCode($code);
			$this->cache->set($entryIdentifier, $syntaxTree);
		} else {
			$syntaxTree = $this->cache->get($entryIdentifier);
		}
		return $syntaxTree;
	}
}