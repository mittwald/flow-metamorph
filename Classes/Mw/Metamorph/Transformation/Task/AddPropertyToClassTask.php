<?php
namespace Mw\Metamorph\Transformation\Task;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\Helper\ASTBasedFileRefactoring;
use Mw\Metamorph\Transformation\Helper\ClosureVisitor;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class AddPropertyToClassTask implements TaskInterface {

	/** @var string */
	private $targetClassName;

	/** @var string */
	private $propertyName;

	/** @var string */
	private $propertyType;

	/** @var string */
	private $propertyIsPublic;

	/** @var array */
	private $propertyAnnotations;

	/** @var BuilderFactory */
	private $factory;

	/**
	 * @var ASTBasedFileRefactoring
	 * @Flow\Inject
	 */
	protected $refactoring;

	public function __construct(
		$targetClassName,
		$propertyName,
		$propertyType,
		$propertyIsPublic,
		array $propertyAnnotations = []
	) {
		$this->targetClassName     = $targetClassName;
		$this->propertyName        = $propertyName;
		$this->propertyType        = $propertyType;
		$this->propertyIsPublic    = $propertyIsPublic;
		$this->propertyAnnotations = $propertyAnnotations;

		$this->factory = new BuilderFactory();
	}

	public function toString() {
		return sprintf(
			'AddPropertyToClass{class="%s", property="%s", type="%s"}',
			$this->targetClassName,
			$this->propertyName,
			$this->propertyType
		);
	}

	public function execute(MorphConfiguration $configuration, TaskQueue $queue) {
		/** @var Node\Stmt\Property $propertyNode */
		$propertyNode = $this->factory
			->property($this->propertyName)
			->{'make' . ($this->propertyIsPublic ? 'Public' : 'Protected')}()
			->getNode();

		$commentNode = $this->buildDocCommentNode();
		$propertyNode->setAttribute('comments', [$commentNode]);

		$visitor = new ClosureVisitor();
		$visitor->setOnLeave(
			function (Node $node) use ($propertyNode) {
				if ($node instanceof Node\Stmt\Class_ && $node->namespacedName->toString() == $this->targetClassName) {
					$stmts             = $node->stmts;
					$lastPropertyIndex = NULL;
					$found             = FALSE;

					foreach ($stmts as $key => $stmt) {
						if (!$stmt instanceof Node\Stmt\Property) {
							continue;
						}

						$lastPropertyIndex = $key;
						foreach ($stmt->props as $prop) {
							if ($prop->name === $this->propertyName) {
								$found = TRUE;
							}
						}
					}

					if (TRUE === $found) {
						return NULL;
					}

					$stmts = $node->stmts;
					if (NULL === $lastPropertyIndex) {
						$stmts = array_merge([$propertyNode], $node->stmts);
					} else {
						array_splice($stmts, $lastPropertyIndex + 1, 0, [$propertyNode]);
					}

					$node->stmts = $stmts;
					return $node;
				}
				return NULL;
			}
		);

		$classMapping = $configuration
			->getClassMappingContainer()
			->getClassMappingByNewClassName($this->targetClassName);

		$this->refactoring->applyVisitorOnFile(
			$classMapping->getTargetFile(),
			$visitor
		);
	}

	/**
	 * @return Doc
	 */
	private function buildDocCommentNode() {
		$commentLines = [
			'/**',
			' * @var ' . $this->propertyType
		];

		foreach ($this->propertyAnnotations as $annotation) {
			$commentLines[] = ' * ' . $annotation;
		}

		$commentLines[] = ' */';

		$comment = implode("\n", $commentLines);

		$commentNode = new Doc($comment);
		return $commentNode;
	}

	public function getHash() {
		return sha1($this->targetClassName . '.' . $this->propertyName);
	}

}