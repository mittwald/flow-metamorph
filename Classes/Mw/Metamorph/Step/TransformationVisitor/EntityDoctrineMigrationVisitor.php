<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class EntityDoctrineMigrationVisitor extends AbstractVisitor {

	/** @var Node\Stmt\Namespace_ */
	private $currentNamespace = NULL;

	/**
	 * @var ClassDefinition
	 */
	private $currentClass;

	private $neededNamespaceImports = [];

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

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Namespace_) {
			$this->currentNamespace       = $node;
			$this->neededNamespaceImports = [];
		}
		if ($node instanceof Node\Stmt\Class_) {
			$classDefinition = $this->classDefinitionContainer->get($node->namespacedName->toString());
			$isEntity        = $this->classIsEntity($node);
			$isValueObject   = $this->classIsValueObject($node);

			$classDefinition->setFact('isEntity', $isEntity);
			$classDefinition->setFact('isValueObject', $isValueObject);
			$classDefinition->setFact('isEntitySuperclass', $this->classIsEntitySuperclass($node));
			$classDefinition->setFact('isValueObjectSuperclass', $this->classIsValueObjectSuperclass($node));

			$this->currentClass = $classDefinition;
		}

		return NULL;
	}

	private function classIsDirectEntityOrValueObjectDescendant(Node\Stmt\Class_ $node) {
		$definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
		$parentName = $definition->getParentClass() ? $definition->getParentClass()->getFullyQualifiedName() : '';
		return
			$parentName === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity' ||
			$parentName === 'Tx_Extbase_DomainObject_AbstractEntity' ||
			$parentName === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject' ||
			$parentName === 'Tx_Extbase_DomainObject_AbstractValueObject';
	}

	private function classIsEntity(Node\Stmt\Class_ $node) {
		return /*!$node->isAbstract() &&*/
			$this->classIsEntitySuperclass($node);
	}

	private function classIsValueObject(Node\Stmt\Class_ $node) {
		return /*!$node->isAbstract() &&*/
			$this->classIsValueObjectSuperclass($node);
	}

	private function classIsValueObjectSuperclass(Node\Stmt\Class_ $node) {
		$definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
		return $definition && (
			$definition->doesInherit('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject') ||
			$definition->doesInherit('Tx_Extbase_DomainObject_AbstractValueObject')
		);
	}

	private function classIsEntitySuperclass(Node\Stmt\Class_ $node) {
		$definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
		return $definition && (
			$definition->doesInherit('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity') ||
			$definition->doesInherit('Tx_Extbase_DomainObject_AbstractEntity')
		);
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\Stmt\Namespace_ && count($this->neededNamespaceImports)) {
			foreach ($this->neededNamespaceImports as $alias => $namespace) {
				$node = $this->importHelper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
			}
			return $node;
		} else if ($node instanceof Node\Stmt\If_) {
			$cond = $node->cond;
			if ($cond instanceof Node\Expr\Instanceof_) {
				if ($cond->class == 'TYPO3\\CMS\\Extbase\\Persistence\\LazyLoadingProxy') {
					return FALSE;
				}
			}
		} else if ($node instanceof Node\Stmt\Class_) {
			$classDefinition = $this->currentClass;
			$annotation      = NULL;
			$isEntity        = $classDefinition->getFact('isEntity');
			$isValueObject   = $classDefinition->getFact('isValueObject');

			if ($isEntity || $isValueObject) {
				$annotation = new AnnotationRenderer('Flow', 'Entity');
			}

			if ($this->classIsDirectEntityOrValueObjectDescendant($node)) {
				$node->extends = NULL;
			}

			if (NULL !== $annotation) {
				$comment = $this->getCommentForNode($node);

				$this->neededNamespaceImports['Flow'] = 'TYPO3\\Flow\\Annotations';
				$this->commentModifier->addAnnotationToDocComment($comment, $annotation);
			}

			if (($isEntity || $isValueObject) && $classDefinition->getFact('isAbstract')) {
				$comment = $this->getCommentForNode($node);

				$annotation = new AnnotationRenderer('ORM', 'InheritanceType');
				$annotation->setArgument('JOINED');

				$this->neededNamespaceImports['ORM'] = 'Doctrine\\ORM\\Mapping';
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
