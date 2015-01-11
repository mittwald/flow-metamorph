<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Node;

class ClassNamespaceRewriterVisitor extends AbstractVisitor {

	/** @var \PhpParser\Node\Stmt\Namespace_ */
	protected $currentNamespaceNode = NULL;

	/** @var \PhpParser\Node\Stmt\Class_ */
	protected $currentClassNode = NULL;

	protected $newNamespace = NULL;

	protected $imports = [];

	protected $definedClasses = [];

	public function beforeTraverse(array $nodes) {
		$this->imports              = [];
		$this->currentNamespaceNode = NULL;
		$this->newNamespace         = NULL;
	}

	public function enterNode(Node $node) {
		if ($node->getDocComment()) {
			$this->replaceOldClassnamesInDocComment($node);
		}

		if ($node instanceof Node\Stmt\Namespace_) {
			$this->currentNamespaceNode = $node;
		} elseif ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
			return $this->convertClassNameForOldClass($node);
		} elseif ($node instanceof Node\Name) {
			return $this->convertClassNameForOldClassUsage($node);
		} elseif ($node instanceof Node\Scalar\String) {
			return $this->convertClassNameInString($node);
		}
		return NULL;
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Stmt\Namespace_) {
			$this->currentNamespaceNode = NULL;
			if ($this->newNamespace !== NULL) {
				$uses = $this->getUseStatements();

				$node->name  = $this->newNamespace;
				$node->stmts = array_merge($uses, $node->stmts);

				$this->newNamespace = NULL;
				return $node;
			}
		}
		return NULL;
	}

	public function afterTraverse(array $nodes) {
		if (NULL !== $this->newNamespace) {
			$useNodes      = $this->getUseStatements();
			$namespaceNode = new Node\Stmt\Namespace_($this->newNamespace, array_merge($useNodes, $nodes));

			return [$namespaceNode];
		}
		return NULL;
	}

	private function getNamespaceAndRelativeNameForOldClass($oldClass) {
		$newName           = $this->classMap->getClassMapping($oldClass)->getNewClassName();
		$newNameComponents = explode('\\', $newName);

		$relativeClassName = array_pop($newNameComponents);
		$namespace         = implode('\\', $newNameComponents);

		return [$namespace, $relativeClassName];
	}

	private function getUseStatements() {
		$uses = [];

		foreach ($this->imports as $fqcn => $name) {
			$useuse = new Node\Stmt\UseUse($name);
			$uses[] = new Node\Stmt\Use_([$useuse]);
		}

		return $uses;
	}

	/**
	 * @param Node $node
	 * @return array
	 */
	public function replaceOldClassnamesInDocComment(Node $node) {
		$text = $node->getDocComment()->getText();

		foreach ($this->classMap->getClassMappings() as $classMapping) {
			$new = '\\' . $classMapping->getNewClassName();
			$old = $classMapping->getOldClassName();

			if (strpos($text, $old) !== FALSE) {
				$text = str_replace('\\' . $old, $new, $text);
				$text = str_replace($old, $new, $text);
			}
		}

		$node->getDocComment()->setText($text);
	}

	/**
	 * @param Node $node
	 * @return null|Node
	 */
	private function convertClassNameForOldClass(Node $node) {
		if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
			$this->currentClassNode = $node;

			/** @noinspection PhpUndefinedFieldInspection */
			$oldName = $node->namespacedName->toString();

			if ($this->classMap->hasClassMapping($oldName)) {
				list($namespace, $relativeClassName) = $this->getNamespaceAndRelativeNameForOldClass($oldName);

				$node->name           = new Node\Name($relativeClassName);
				$node->namespacedName = new Node\Name($namespace . '\\' . $relativeClassName);

				$this->newNamespace = new Node\Name($namespace);
				return $node;
			}
		}
		return NULL;
	}

	/**
	 * @param Node\Name $node
	 * @return Node\Name
	 */
	private function convertClassNameForOldClassUsage(Node\Name $node) {
		$oldName = $node->toString();
		if ($this->classMap->hasClassMapping($oldName)) {
			list($namespace, $relativeClassName) = $this->getNamespaceAndRelativeNameForOldClass($oldName);
			$fqcn = $namespace . '\\' . $relativeClassName;

			if ($this->currentClassNode === NULL || $this->currentClassNode->namespacedName->toString() != $fqcn) {
				$this->imports[$fqcn] = new Node\Name($fqcn);
			}
			return new Node\Name($relativeClassName);
		}
		return NULL;
	}

	/**
	 * @param Node\Scalar\String $node
	 * @return Node
	 */
	private function convertClassNameInString(Node\Scalar\String $node) {
		$text = $node->value;
		foreach ($this->classMap->getClassMappings() as $classMapping) {
			$old = $classMapping->getOldClassName();
			$new = $classMapping->getNewClassName();
			if (strpos($text, $old) !== FALSE) {
				$text = str_replace($old, $new, $text);
			}
		}
		$node->value = $text;
		return NULL;
	}

}