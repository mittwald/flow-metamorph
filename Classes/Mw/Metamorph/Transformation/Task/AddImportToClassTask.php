<?php
namespace Mw\Metamorph\Transformation\Task;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\Helper\ASTBasedFileRefactoring;
use Mw\Metamorph\Transformation\Helper\ClosureVisitor;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class AddImportToClassTask implements TaskInterface {

	private $class;

	private $import;

	private $alias = NULL;

	/**
	 * @var ImportHelper
	 * @Flow\Inject
	 */
	protected $importHelper;

	/**
	 * @var ASTBasedFileRefactoring
	 * @Flow\Inject
	 */
	protected $refactoring;

	public function __construct($class, $namespace, $alias = NULL) {
		$this->class  = $class;
		$this->import = $namespace;
		$this->alias  = $alias;
	}

	public function execute(MorphConfiguration $configuration, TaskQueue $queue) {
		$foundClass    = FALSE;
		$namespaceNode = NULL;

		$visitor = new ClosureVisitor();
		$visitor->setOnEnter(
			function (Node $node) use (&$foundClass, &$namespaceNode) {
				if ($node instanceof Node\Stmt\Namespace_) {
					$namespaceNode = $node;
				} elseif ($node instanceof Node\Stmt\Class_ && $node->namespacedName->toString() == $this->class) {
					$foundClass = TRUE;
				}
			}
		);

		$visitor->setOnLeave(
			function (Node $node) use (&$foundClass, &$namespaceNode) {
				if ($node instanceof Node\Stmt\Namespace_ && $foundClass) {
					return $this->importHelper->importNamespaceIntoOtherNamespace(
						$node,
						$this->import,
						$this->alias
					);
				}
				return NULL;
			}
		);

		$classMapping = $configuration
			->getClassMappingContainer()
			->getClassMappingByNewClassName($this->class);

		$this->refactoring->applyVisitorOnFile(
			$classMapping->getTargetFile(),
			$visitor
		);
	}

	public function toString() {
		return sprintf(
			'AddImportToClass{class="%s", namespace="%s", alias="%s"}',
			$this->class,
			$this->import,
			$this->alias
		);
	}

	public function getHash() {
		return sha1($this->class . '::' . $this->import . '::' . $this->alias);
	}

}