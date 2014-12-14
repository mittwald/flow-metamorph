<?php
namespace Mw\Metamorph\Transformation\Analyzer;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionDeferred;
use Mw\Metamorph\Domain\Model\Definition\PropertyDefinition;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;

class AnalyzerVisitor extends NodeVisitorAbstract {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var string
	 */
	private $currentNamespace = '';

	/**
	 * @var ClassDefinition
	 */
	private $currentClassDefinition = NULL;

	/**
	 * @var ClassMappingContainer
	 */
	private $mappingContainer;

	public function __construct(ClassMappingContainer $container) {
		$this->mappingContainer = $container;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Namespace_) {
			$this->currentNamespace = $node->name->toString();
		} elseif ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
			$this->currentClassDefinition = $this->generateClassDefinition($node);
		} elseif ($node instanceof Node\Stmt\Property) {
			$this->addPropertyDefinitionsToClass($node);
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
			$this->classDefinitionContainer->add($this->currentClassDefinition);
			$this->currentClassDefinition = NULL;
		}
	}

	private function splitNameIntoClassAndNamespace(Node\Name $node) {
		$parts = $node->parts;

		$class     = array_pop($parts);
		$namespace = implode('\\', $parts);

		return [$class, $namespace];
	}

	/**
	 * @param Node $node
	 * @return ClassDefinition
	 */
	private function generateClassDefinition(Node $node) {
		if (!($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_)) {
			throw new \InvalidArgumentException('$node must be a class or interface node!');
		}

		$mapping = $this->mappingContainer->getClassMappingByNewClassName(
			$this->currentNamespace . '\\' . $node->name
		);

		$classDef = new ClassDefinition($node->name, $this->currentNamespace);
		$classDef->setClassMapping($mapping);

		if (NULL !== $node->extends && [] !== $node->extends) {
			list($class, $namespace) = $this->splitNameIntoClassAndNamespace($node->extends);
			$classDef->setParentClass(new ClassDefinitionDeferred($class, $namespace));
		}

		if ($node instanceof Node\Stmt\Class_) {
			if ($node->implements) {
				foreach ($node->implements as $interface) {
					list($class, $namespace) = $this->splitNameIntoClassAndNamespace($interface);
					$classDef->addInterface(new ClassDefinitionDeferred($class, $namespace));
				}
			}

			$classDef->setFact('isAbstract', $node->isAbstract());
			$classDef->setFact('isFinal', $node->isFinal());
		}
		return $classDef;
	}

	/**
	 * @param Node\Stmt\Property $node
	 * @return void
	 */
	private function addPropertyDefinitionsToClass(Node\Stmt\Property $node) {
		foreach ($node->props as $subProp) {
			$this->currentClassDefinition->addProperty(
				new PropertyDefinition(
					$subProp->name,
					$subProp->getDocComment() ? $subProp->getDocComment()->getReformattedText() : NULL
				)
			);
		}
	}

}