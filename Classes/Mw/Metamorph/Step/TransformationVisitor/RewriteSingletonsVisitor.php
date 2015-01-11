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

class RewriteSingletonsVisitor extends AbstractVisitor {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

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

	public function leaveNode(Node $node) {
		if (!$node instanceof Node\Stmt\Class_) {
			return NULL;
		}

		/** @noinspection PhpUndefinedFieldInspection */
		$name       = $node->namespacedName->toString();
		$definition = $this->classDefinitionContainer->get($name);

		if ($definition && $this->isSingleton($definition)) {
			$implementsList = $node->implements;
			foreach ($implementsList as $key => $implements) {
				if ($implements->toString() === 't3lib_Singleton' || $implements->toString(
					) === 'TYPO3\\CMS\\Core\\SingletonInterface'
				) {
					unset($implementsList[$key]);
				}
			}
			$node->implements = array_values($implementsList);

			$comment = $node->getDocComment();
			if (NULL === $comment) {
				$comments   = $node->getAttribute('comments', []);
				$comments[] = $comment = new Doc("/**\n */");

				$node->setAttribute('comments', $comments);
			}

			$this->commentModifier->addAnnotationToDocComment(
				$comment,
				(new AnnotationRenderer('Flow', 'Scope'))->setArgument('singleton')
			);

			$this->taskQueue->enqueue(
				(new AddImportToClassTaskBuilder())
					->setTargetClassName($node->namespacedName->toString())
					->setImport('TYPO3\\Flow\\Annotations')
					->setNamespaceAlias('Flow')
					->buildTask()
			);

			return $node;
		}

		return NULL;
	}

	private function isSingleton(ClassDefinition $classDefinition) {
		return
			$classDefinition->doesImplement('t3lib_Singleton') ||
			$classDefinition->doesImplement('TYPO3\\CMS\\Core\\SingletonInterface') ||
			$classDefinition->doesInherit('TYPO3\\CMS\\Extbase\\Persistence\\Repository');
	}

}