<?php
namespace Mw\Metamorph\Transformation\Helper;

use Mw\Metamorph\Parser\ParserInterface;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\PrettyPrinterAbstract;
use TYPO3\Flow\Annotations as Flow;

class ASTBasedFileRefactoring {

	/**
	 * @var ParserInterface
	 * @Flow\Inject
	 */
	protected $parser;

	/**
	 * @var PrettyPrinterAbstract
	 * @Flow\Inject
	 */
	protected $printer;

	public function applyVisitorOnFile($fileName, NodeVisitor $visitor) {
		$traverser = new NodeTraverser();
		$traverser->addVisitor(new NodeVisitor\NameResolver());
		$traverser->addVisitor($visitor);

		$stmts = $this->parser->parseFile($fileName);
		$stmts = $traverser->traverse($stmts);

		file_put_contents($fileName, $this->printer->prettyPrintFile($stmts));
	}

}