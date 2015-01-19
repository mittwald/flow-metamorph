<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Step\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Step\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\CommentHelper;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class ReplaceMakeInstanceCallsVisitor extends AbstractVisitor {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var CommentHelper
	 * @Flow\Inject
	 */
	protected $commentHelper;

	/**
	 * @var Node\Stmt\Class_
	 */
	private $currentClass;

	private $addBeforeStmt = NULL;

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$this->currentClass = $node;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Expr\StaticCall) {
			if ($node->class instanceof Node\Name\FullyQualified &&
				$this->isGeneralUtilityClass($node->class) &&
				$node->name === 'makeInstance'
			) {
				$args = $node->args;

				$className         = array_shift($args)->value;
				$classNameIsStatic = $className instanceof Node\Scalar\String;

				// This gets complicated, so read carefully:
				//
				// - IF the class name is static (i.e. a string literal), we can check whether the class
				//   is singleton-scoped or not.
				//   - IF the class is singleton-scoped, replace it with a dependency injection.
				//   - IF the class is prototype-scoped, replace it with a constructor call
				//   - IF the class in unknown, we also don't know the scope, so we guess:
				//       - IF constructor arguments are passed, assume the class is prototype-scoped and
				//         replace it with a constructor call.
				//       - OTHERWISE replace it with an object manager call
				// - IF the class name is dynamic (i.e. an expression), we have no idea whether the class
				//   is singleton-scoped.
				//   - IF makeInstance is called with constructor arguments, we ASSUME that the class is
				//     prototype-scoped and replace the call with a dynamic constructor call.
				//   - OTHERWISE, we COULD use a dynamic constructor call (like `new $foo()`). This, however
				//     is dangerous, because `$foo` might be singleton-scoped. So, in this case, we just
				//     inject the ObjectManager into the class and retrieve the instance from there. This
				//     is not very elegant because of the dependency, but it's the only safe way.
				if ($classNameIsStatic) {
					$className       = new Node\Name\FullyQualified($className->value);
					$classDefinition = $this->classDefinitionContainer->get($className->toString());

					if ($classDefinition) {
						if ($classDefinition->getFact('isSingleton')) {
							return $this->replaceWithDependencyInjection($className);
						} else {
							return $this->replaceWithConstructorCall($className, $args);
						}
					} else {
						if (count($args) > 0) {
							return $this->replaceWithConstructorCall($className, $args);
						} else {
							return $this->replaceWithObjectManagerCall($node);
						}
					}
				} else {
					if (count($args) > 0) {
						return $this->replaceWithDynamicConstructorCall($className, $args);
					} else {
						return $this->replaceWithObjectManagerCall($node);
					}
				}
			}
		} else if ($node instanceof Node\Stmt && $this->addBeforeStmt !== NULL) {
			$stmt                = $this->addBeforeStmt;
			$this->addBeforeStmt = NULL;

			if ($node instanceof Node\Stmt\ClassMethod) {
				$node->stmts = array_merge([$stmt, $node->stmts]);
				return $node;
			} else {
				return new Node\Stmt\If_(
					new Node\Expr\ConstFetch(new Node\Name('TRUE')),
					['stmts' => [$stmt, $node]]
				);
			}
		}
		return NULL;
	}

	private function isGeneralUtilityClass(Node\Name $name) {
		return (
			$name == 'Mw\\T3Compat\\Utility\\GeneralUtility' ||
			$name == 't3lib_div' ||
			$name == 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility'
		);
	}

	/**
	 * @param Node\Name $className
	 * @return Node\Expr\PropertyFetch
	 */
	private function replaceWithDependencyInjection(Node\Name $className) {
		$propertyName = '_metamorph_autoInject_' . substr(sha1($className->toString()), 0, 8);

		$this->taskQueue->enqueue(
			(new AddPropertyToClassTaskBuilder())
				->setTargetClassName($this->currentClass->namespacedName->toString())
				->setPropertyName($propertyName)
				->setPropertyType('\\' . ltrim($className->toString(), '\\'))
				->addAnnotation(new AnnotationRenderer('Flow', 'Inject'))
				->buildTask()
		);

		$this->taskQueue->enqueue(
			(new AddImportToClassTaskBuilder())
				->importFlowAnnotations($this->currentClass->namespacedName->toString())
				->buildTask()
		);

		return new Node\Expr\PropertyFetch(
			new Node\Expr\Variable('this'),
			$propertyName
		);
	}

	/**
	 * @param $className
	 * @param $args
	 * @return Node\Expr\New_
	 */
	private function replaceWithConstructorCall($className, $args) {
		return new Node\Expr\New_($className, $args);
	}

	/**
	 * @param Node $node
	 * @return Node\Expr\MethodCall
	 */
	private function replaceWithObjectManagerCall(Node $node) {
		$this->taskQueue->enqueue(
			(new AddPropertyToClassTaskBuilder())
				->setTargetClassName($this->currentClass->namespacedName->toString())
				->setPropertyName('objectManager')
				->setPropertyType('\\TYPO3\\Flow\\Object\\ObjectManagerInterface')
				->addAnnotation(new AnnotationRenderer('Flow', 'Inject'))
				->buildTask()
		);

		$this->taskQueue->enqueue(
			(new AddImportToClassTaskBuilder())
				->importFlowAnnotations($this->currentClass->namespacedName->toString())
				->buildTask()
		);

		return new Node\Expr\MethodCall(
			new Node\Expr\PropertyFetch(
				new Node\Expr\Variable('this'),
				'objectManager'
			),
			'get',
			$node->args
		);
	}

	/**
	 * @param $className
	 * @param $args
	 * @return Node\Expr\New_
	 */
	private function replaceWithDynamicConstructorCall($className, $args) {
		$variableName       = '_' . sha1(serialize($className));
		$variable           = new Node\Expr\Variable($variableName);
		$variableAssignment = new Node\Expr\Assign(
			$variable,
			$className
		);

		// Usually, we could simply return an array of nodes here to insert the assignment
		// statement directly before the `new` statement. However, if multiple visitors are
		// assigned to the same NodeTraverser, these will have their `leaveNode` method
		// called with an array of nodes, creating a fatal error.
		$this->addBeforeStmt = $variableAssignment;
		return new Node\Expr\New_($variable, $args);
	}

}