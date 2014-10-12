<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;


class ExtbaseClassEliminationVisitor extends AbstractVisitor
{



    /** @var Node\Stmt\Namespace_ */
    private $currentNamespace = NULL;


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



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace       = $node;
            $this->neededNamespaceImports = [];
        }
        if ($node instanceof Node\Stmt\Class_)
        {
            $annotation    = NULL;
            $isEntity      = $this->classIsEntity($node);
            $isValueObject = $this->classIsValueObject($node);

            if ($isEntity || $isValueObject)
            {
                if ($this->classIsDirectEntityDescendant($node))
                {
                    $node->extends = NULL;
                }
                $annotation = new AnnotationRenderer('Flow', $isEntity ? 'Entity' : 'ValueObject');
            }

            if (NULL !== $annotation)
            {
                $comment = $node->getDocComment();
                if (NULL === $comment)
                {
                    $comments   = $node->getAttribute('comments', []);
                    $comments[] = $comment = new Doc("/**\n */");

                    $node->setAttribute('comments', $comments);
                }

                $this->neededNamespaceImports['Flow'] = 'TYPO3\\Flow\\Annotations';
                $this->commentModifier->addAnnotationToDocComment($comment, $annotation);
            }
        }
    }



    private function classIsDirectEntityDescendant(Node\Stmt\Class_ $node)
    {
        $definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
        $parentName = $definition->getParentClass() ? $definition->getParentClass()->getFullyQualifiedName() : '';
        return
            $parentName === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity' ||
            $parentName === 'Tx_Extbase_DomainObject_AbstractEntity' ||
            $parentName === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject' ||
            $parentName === 'Tx_Extbase_DomainObject_AbstractValueObject';
    }



    private function classIsEntity(Node\Stmt\Class_ $node)
    {
        $definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
        return !$node->isAbstract() && $definition && (
            $definition->doesInherit('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity') ||
            $definition->doesInherit('Tx_Extbase_DomainObject_AbstractEntity')
        );
    }



    private function classIsValueObject(Node\Stmt\Class_ $node)
    {
        $definition = $this->classDefinitionContainer->get($node->namespacedName->toString());
        return !$node->isAbstract() && $definition && (
            $definition->doesInherit('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject') ||
            $definition->doesInherit('Tx_Extbase_DomainObject_AbstractValueObject')
        );
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_ && count($this->neededNamespaceImports))
        {
            foreach ($this->neededNamespaceImports as $alias => $namespace)
            {
                $node = $this->importHelper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
            }
            return $node;
        }
        if ($node instanceof Node\Stmt\If_)
        {
            $cond = $node->cond;
            if ($cond instanceof Node\Expr\Instanceof_)
            {
                if ($cond->class == 'TYPO3\\CMS\\Extbase\\Persistence\\LazyLoadingProxy')
                {
                    return FALSE;
                }
            }
        }
        return NULL;
    }

}