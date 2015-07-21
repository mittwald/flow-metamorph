<?php
namespace Mw\Metamorph\Parser\PHP;

use Mw\Metamorph\Parser\CachingDecorator;
use PhpParser\Lexer;
use PhpParser\Parser;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Parser\PHP
 *
 * @scope("singleton")
 */
class PhpParserFactory {

	public function getParser($cached = FALSE) {
		$phpParser = new Parser\Php5(new Lexer());
		$adapter   = new PhpParser($phpParser);

		if ($cached) {
			$adapter = new CachingDecorator($adapter);
		}

		return $adapter;
	}

}