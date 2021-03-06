<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Parser\PHP\NodeWrapper;
use Mw\Metamorph\Step\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Step\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractClassMemberVisitor;
use PhpParser\Node;

class DatabaseAccessVisitor extends AbstractClassMemberVisitor {

	protected $filters = [
		'isGlobalsAccess("TYPO3_DB")'
	];

	/**
	 * @param NodeWrapper $node
	 * @return array|null|Node|void
	 */
	protected function leaveClassMemberNode(NodeWrapper $node) {
		$result = new Node\Expr\PropertyFetch(
			new Node\Expr\Variable('this'),
			'databaseBackend'
		);

		$this->addDatabaseInjectionToCurrentClass();
		return $result;
	}

	protected function leaveClassNode(NodeWrapper $node) {
		if ($node->e('isClassDefinition().inherits("Mw\\T3Compat\\Database\\DatabaseConnection")')) {
			/** @var Node\Stmt\Class_ $real */
			$real          = $node->node();
			$real->extends = new Node\Name\FullyQualified(['Mw', 'T3Compat', 'Database', 'DatabaseConnectionImpl']);

			return $real;
		} else {
			return NULL;
		}
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

		$this->taskQueue->enqueue(
			(new AddImportToClassTaskBuilder())
				->importFlowAnnotations($this->currentClass->getFullyQualifiedName())
				->buildTask()
		);
	}

}