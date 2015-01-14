<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Parser\PHP\NodeWrapper;
use Mw\Metamorph\Step\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractClassMemberVisitor;
use PhpParser\Node;

class DatabaseAccessVisitor extends AbstractClassMemberVisitor {

	protected $filters = [
		'isMethodCall().onObject().isGlobalsAccess("TYPO3_DB")'
	];

	/**
	 * @param NodeWrapper $node
	 * @return array|null|Node|void
	 */
	protected function leaveClassMemberNode(NodeWrapper $node) {
		/** @var Node\Expr\MethodCall $call */
		$call = $node->node();

		$call->var = new Node\Expr\PropertyFetch(
			new Node\Expr\Variable('this'),
			'databaseBackend'
		);

		$this->addDatabaseInjectionToCurrentClass();
		return $call;
	}

	protected function addDatabaseInjectionToCurrentClass() {
		$this->taskQueue->enqueue(
			(new AddPropertyToClassTaskBuilder())
				->setTargetClassName($this->currentClass->getFullyQualifiedName())
				->setPropertyName('databaseBackend')
				->setProtected()
				->addAnnotation(new AnnotationRenderer('Flow', 'Inject'))
				->setPropertyType('\\Mw\\T3Compat\\Database\\DatabaseConnection')
				->buildTask()
		);
	}

}