<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


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



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace       = $node;
            $this->neededNamespaceImports = [];
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
                $comment = $node->getDocComment();
                if (NULL === $comment)
                {
                    $comments   = $node->getAttribute('comments', []);
                    $comments[] = $comment = new Doc("/**\n */");

                    $node->setAttribute('comments', $comments);
                }

                $this->neededNamespaceImports['Flow'] = 'TYPO3\\Flow\\Annotations';
                $this->commentModifier->addAnnotationToDocComment(
                    $comment,
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