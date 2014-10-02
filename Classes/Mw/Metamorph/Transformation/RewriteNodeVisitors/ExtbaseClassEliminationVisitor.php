<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;


class ExtbaseClassEliminationVisitor extends AbstractVisitor
{



    /**
     * @var \PhpParser\Node\Stmt\Namespace_[]
     */
    private $namespaceNodes = [];


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



    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $key => $node)
        {
            if ($node instanceof Node\Stmt\Namespace_)
            {
                $nodes[$key] = $this->importHelper->importNamespaceIntoOtherNamespace(
                    $node,
                    'TYPO3\\Flow\\Annotations',
                    'Flow'
                );
            }
        }
        return $nodes;
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->namespaceNodes[] = $node;
        }
        if ($node instanceof Node\Stmt\Class_)
        {
            $annotation = NULL;

            if ($this->classIsEntity($node))
            {
                $node->extends = NULL;
                $annotation    = new AnnotationRenderer('Flow', 'Entity');
            }
            else if ($this->classIsValueObject($node))
            {
                $node->extends = NULL;
                $annotation    = new AnnotationRenderer('Flow', 'ValueObject');
            }

            if (NULL !== $annotation)
            {
                $this->commentModifier->addAnnotationToDocComment(
                    $node->getDocComment(),
                    new AnnotationRenderer('Flow', 'Entity')
                );
            }
        }
    }



    private function classIsEntity(Node\Stmt\Class_ $node)
    {
        return $node->extends && (
            $node->extends->toString() === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity' ||
            $node->extends->toString() === 'Tx_Extbase_DomainObject_AbstractEntity'
        );
    }



    private function classIsValueObject(Node\Stmt\Class_ $node)
    {
        return $node->extends && (
            $node->extends->toString() === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject' ||
            $node->extends->toString() === 'Tx_Extbase_DomainObject_AbstractValueObject'
        );
    }



    public function leaveNode(Node $node)
    {
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