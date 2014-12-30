<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;

use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\MappingHelper;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Transformation\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;

/**
 * Base class for database migration refactoring visitors.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Visitor
 */
class AbstractMigrationVisitor extends NodeVisitorAbstract {

	/**
	 * @var Tca
	 */
	protected $tca;

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var DocCommentModifier
	 * @Flow\Inject
	 */
	protected $commentHelper;

	/**
	 * @var ClassDefinition
	 */
	protected $currentClass;

	/**
	 * @var array
	 */
	protected $currentTca;

	/**
	 * @var string
	 */
	protected $currentTable;

	/**
	 * @var TaskQueue
	 */
	protected $taskQueue;

	/**
	 * @var MappingHelper
	 */
	protected $mappingHelper;

	public function __construct(Tca $tca, TaskQueue $taskQueue) {
		$this->tca           = $tca;
		$this->taskQueue     = $taskQueue;
		$this->mappingHelper = new MappingHelper($tca);
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$newClassName    = $node->namespacedName->toString();
			$classDefinition = $this->classDefinitionContainer->get($newClassName);
			$classMapping    = $classDefinition->getClassMapping();

			if (NULL === $classMapping) {
				throw new \Exception('No class mapping found for class ' . $newClassName);
			}

			$this->currentTca = [];
			$this->mappingHelper->getTcaForClass(
				new String($newClassName),
				new String($classMapping->getOldClassName()),
				$this->currentTca,
				$this->currentTable
			);

			$this->currentClass = $classDefinition;

			// Remove any kind of domain object annotation when no database
			// mapping is configured for a class.
			if ($this->currentTable === NULL && $this->currentClass->getFact('isAbstract') === FALSE) {
				$comment = $node->getDocComment();
				if (NULL != $comment) {
					$this->commentHelper->removeAnnotationFromDocComment($comment, '@Flow\\Entity');
					$this->commentHelper->removeAnnotationFromDocComment($comment, '@Flow\\ValueObject');
				}
				return $node;
			}
		}

		return NULL;
	}

	protected function isTcaColumnManyToOneRelation(array $configuration) {
		if ($configuration['type'] === 'select') {
			if (!isset($configuration['maxitems']) || $configuration['maxitems'] == 1) {
				return TRUE;
			}
		} elseif ($configuration['type'] === 'group' && $configuration['internal_type'] === 'db') {
			if (isset($configuration['maxitems']) && $configuration['maxitems'] == 1) {
				return TRUE;
			}
		}
		return FALSE;
	}

	protected function isTcaColumnOneToManyRelation(array $configuration) {
		switch ($configuration['type']) {
			case 'select':
				if (isset($configuration['maxitems']) && $configuration['maxitems'] > 1) {
					return TRUE;
				}
				break;

			case 'group':
				if ($configuration['internal_type'] === 'db' && (!isset($configuration['maxitems']) || $configuration['maxitems'] > 1)) {
					return TRUE;
				}
				break;

			case 'inline':
				if (!isset($configuration['maxitems']) || $configuration['maxitems'] > 1) {
					return TRUE;
				}
				break;
		}
		return FALSE;
	}

	protected function isTcaColumnOneToOneRelation(array $configuration) {
		switch ($configuration['type']) {
			case 'inline':
				if (isset($configuration['maxitems']) && $configuration['maxitems'] == 1) {
					return TRUE;
				}
				break;
		}
		return FALSE;
	}

	protected function isTcaColumnManyToManyRelation(array $configuration) {
		switch ($configuration['type']) {
			case 'select':
			case 'group':
				if (isset($configuration['MM'])) {
					return TRUE;
				}
				break;
		}
		return FALSE;
	}

	/**
	 * @param $propertyName
	 * @param $propertyConfig
	 * @param $foreignPropertyName
	 */
	protected function introduceInversePropertyIfNecessary($propertyName, $propertyConfig, $foreignPropertyName) {
		$targetClass = $this->mappingHelper->getClassForTable($propertyConfig['foreign_table']);
		if (!$targetClass->hasProperty($foreignPropertyName)) {
			$inverseAnnotation = new AnnotationRenderer('ORM', 'ManyToOne');
			$inverseAnnotation->addParameter('inversedBy', "{$propertyName}");

			$this->taskQueue->enqueue(
				(new AddPropertyToClassTaskBuilder())
					->setTargetClassName($targetClass->getFullyQualifiedName())
					->setPropertyName("$foreignPropertyName")
					->setPropertyType('\\' . $this->currentClass->getFullyQualifiedName())
					->setProtected()
					->addAnnotation($inverseAnnotation->render())
					->buildTask()
			);

			$this->taskQueue->enqueue(
				(new AddImportToClassTaskBuilder())
					->setTargetClassName($targetClass->getFullyQualifiedName())
					->setImport('Doctrine\\ORM\\Mapping')
					->setNamespaceAlias('ORM')
					->buildTask()
			);
		}
	}

	/**
	 * @param $propertyConfig
	 * @return AnnotationRenderer
	 */
	protected function getAnnotationRendererForPropertyConfiguration($propertyConfig) {
		if ($this->isTcaColumnManyToManyRelation($propertyConfig)) {
			return new AnnotationRenderer('ORM', 'ManyToMany');
		} else if ($this->isTcaColumnManyToOneRelation($propertyConfig)) {
			return new AnnotationRenderer('ORM', 'ManyToOne');
		} else if ($this->isTcaColumnOneToManyRelation($propertyConfig)) {
			return new AnnotationRenderer('ORM', 'OneToMany');
		} else if ($this->isTcaColumnOneToOneRelation($propertyConfig)) {
			return new AnnotationRenderer('ORM', 'OneToOne');
		}
		return NULL;
	}

	/**
	 * @param Node $node
	 * @return Doc
	 */
	protected function getOrCreateNodeDocComment(Node $node) {
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