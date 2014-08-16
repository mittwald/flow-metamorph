<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Comment\Doc;
use PhpParser\Node;


class ExtbaseClassEliminationVisitor extends AbstractVisitor
{


    /**
     * @var \PhpParser\Node\Stmt\Namespace_[]
     */
    private $namespaceNodes = [];



    public function afterTraverse(array $nodes)
    {
        $useuse = new Node\Stmt\UseUse(new Node\Name\FullyQualified('TYPO3\\Flow\\Annotations'), 'Flow');
        $use    = new Node\Stmt\Use_([$useuse]);

        foreach ($nodes as $node)
        {
            if ($node instanceof Node\Stmt\Namespace_)
            {
                $node->stmts = array_merge([$use], $node->stmts);
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
            if ($node->extends && ($node->extends->toString() === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity' || $node->extends->toString() === 'Tx_Extbase_DomainObject_AbstractEntity'))
            {
                $this->increaseStatCounter($node->extends->toString());
                $node->extends = NULL;

                $this->addAnnotation($node->getDocComment(), '@Flow\\Entity');
            }
            if ($node->extends && ($node->extends->toString() === 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject' || $node->extends->toString() === 'Tx_Extbase_DomainObject_AbstractValueObject'))
            {
                $this->increaseStatCounter($node->extends->toString());
                $node->extends = NULL;

                $this->addAnnotation($node->getDocComment(), '@Flow\\ValueObject');
            }
        }
    }



    private function increaseStatCounter($name)
    {
//        if (!isset($this->statistics[$name]))
//        {
//            $this->statistics[$name] = 0;
//        }
//        $this->statistics[$name]++;
    }



    private function addAnnotation(Doc $comment, $annotation)
    {
        $text = $comment->getReformattedText();

        $lines = explode("\n", $text);
        $count = count($lines);

        $lines[$count - 1] = ' * ' . $annotation;
        $lines[$count]     = ' */';

        $text = implode("\n", $lines);

        $comment->setText($text);
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
                    $this->increaseStatCounter($cond->class->toString());
                    return FALSE;
                }
            }
        }
    }

}