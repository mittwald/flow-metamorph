<?php
namespace Mw\Metamorph\Transformation\ClassInventory;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ClassFinderVisitor extends NodeVisitorAbstract {

	private $classList;

	private $filename;

	public function __construct(\ArrayAccess $classList, $filename) {
		$this->classList = $classList;
		$this->filename  = $filename;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
			$name                   = $node->namespacedName->toString();
			$this->classList[$name] = $this->filename;
		}
	}

}