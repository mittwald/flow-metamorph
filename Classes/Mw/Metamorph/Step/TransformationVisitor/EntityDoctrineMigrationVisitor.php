<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Step\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class EntityDoctrineMigrationVisitor extends AbstractVisitor {

	/**
	 * @var ImportHelper
	 * @Flow\Inject
	 */
	protected $importHelper;

	/**
	 * @var DocCommentModifier
	 * @Flow\Inject
	 */
	protected $commentModifier;

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var ClassDefinition
	 */
	private $currentClass;

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$classDefinition    = $this->classDefinitionContainer->get($node->namespacedName->toString());
			$this->currentClass = $classDefinition;
		}

		return NULL;
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Stmt\If_) {
			$cond = $node->cond;
			if ($cond instanceof Node\Expr\Instanceof_) {
				if ($cond->class == 'TYPO3\\CMS\\Extbase\\Persistence\\LazyLoadingProxy') {
					return FALSE;
				}
			}
		} else if ($node instanceof Node\Stmt\Class_ && $this->currentClass) {
			$classDefinition       = $this->currentClass;
			$annotation            = NULL;
			$isEntityOrValueObject = $classDefinition->getFact('isEntityOrValueObject');

			if ($isEntityOrValueObject) {
				$annotation = new AnnotationRenderer('Flow', 'Entity');
			}

			if ($classDefinition->getFact('isDirectEntityOrValueObjectDescendant')) {
				$node->extends = NULL;
			}

			if (NULL !== $annotation) {
				$comment = $this->getCommentForNode($node);

				$this->taskQueue->enqueue(
					(new AddImportToClassTaskBuilder())
						->importFlowAnnotations($classDefinition->getFullyQualifiedName())
						->buildTask()
				);
				$this->commentModifier->addAnnotationToDocComment($comment, $annotation);
			}

			if ($isEntityOrValueObject && $classDefinition->getFact('isAbstract')) {
				$comment = $this->getCommentForNode($node);

				$annotation = new AnnotationRenderer('ORM', 'InheritanceType');
				$annotation->setArgument('JOINED');

				$this->taskQueue->enqueue(
					(new AddImportToClassTaskBuilder())
						->setTargetClassName($classDefinition->getFullyQualifiedName())
						->setImport('Doctrine\\ORM\\Mapping')
						->setNamespaceAlias('ORM')
						->buildTask()
				);
				$this->commentModifier->addAnnotationToDocComment($comment, $annotation);
			}

			$this->currentClass = NULL;
			return $node;
		}
		return NULL;
	}

	/**
	 * @param Node $node
	 * @return null|Doc
	 */
	private function getCommentForNode(Node $node) {
		$comment = $node->getDocComment();
		if (NULL === $comment) {
			$comments   = $node->getAttribute('comments', []);
			$comments[] = $comment = new Doc("/**\n */");

			$node->setAttribute('comments', $comments);
			return $comment;
		}
		return $comment;
	}

}
