<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Parser\PHP\NodeWrapper;
use Mw\Metamorph\Step\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Step\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractClassMemberVisitor;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use TYPO3\Flow\Annotations as Flow;

class InjectStaticDependenciesVisitor extends AbstractClassMemberVisitor {

	protected function leaveClassMemberNode(NodeWrapper $node) {
		$nameConstraint = function ($name) {
			$def = $this->classDefinitions->get($name);
			return $def !== NULL;
		};

		if ($node->q()->isStaticMethodCall()->onClass()->isName($nameConstraint)->evaluate()) {
			/** @var StaticCall $call */
			$call = $node->node();

			$class = $call->class->toString();
			$short = lcfirst($call->class->getLast());

			$node = new MethodCall(
				new PropertyFetch(
					new Variable('this'),
					$short
				),
				$call->name,
				$call->args
			);

			$this->taskQueue->enqueue(
				(new AddPropertyToClassTaskBuilder())
					->setTargetClassName($this->currentClass->getFullyQualifiedName())
					->setPropertyName($short)
					->setPropertyType('\\' . $class)
					->addAnnotation(new AnnotationRenderer('Flow', 'Inject'))
					->setProtected()
					->buildTask()
			);

			$this->taskQueue->enqueue(
				(new AddImportToClassTaskBuilder())
					->importFlowAnnotations($this->currentClass->getFullyQualifiedName())
					->buildTask()
			);

			return $node;
		}

		return NULL;
	}

}