<?php
namespace Mw\Metamorph\Transformation\Helper;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class ASTBasedFileRefactoring {

	/** @var Parser */
	private $parser;

	/** @var PrettyPrinterAbstract */
	private $printer;

	public function __construct() {
		$this->parser  = new Parser(new Lexer());
		$this->printer = new Standard();
	}

	public function applyVisitorOnFile($fileName, NodeVisitor $visitor) {
		$code = file_get_contents($fileName);

		$traverser = new NodeTraverser();
		$traverser->addVisitor(new NodeVisitor\NameResolver());
		$traverser->addVisitor($visitor);

		$stmts = $this->parser->parse($code);
		$stmts = $traverser->traverse($stmts);

		file_put_contents($fileName, $this->printer->prettyPrintFile($stmts));
	}

}