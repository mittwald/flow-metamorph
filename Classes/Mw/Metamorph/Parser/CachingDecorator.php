<?php
namespace Mw\Metamorph\Parser;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cache\Frontend\FrontendInterface;

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

	public function __construct(ParserInterface $actualParser) {
		$this->actualParser = $actualParser;
	}

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