<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Node;


class ReplaceMakeInstanceCallsVisitor extends AbstractVisitor
{



    private $imports = [];



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\StaticCall)
        {
            if ($node->class instanceof Node\Name\FullyQualified && ($node->class == 'Mw\\Metamorph\\TYPO3\\Utility\\GeneralUtility') && $node->name === 'makeInstance')
            {
                $args = $node->args;

                $className = array_shift($args)->value;
                if ($className instanceof Node\Scalar\String)
                {
                    $this->imports[$className->value] = new Node\Name($className->value);
                    $components                       = explode('\\', $className->value);
                    $className                        = new Node\Name(array_pop($components));
                }

                return new Node\Expr\New_($className, $args);
            }
        }
        elseif ($node instanceof Node\Stmt\Namespace_)
        {
            $uses = [];
            foreach ($this->imports as $import)
            {
                $useuse = new Node\Stmt\UseUse($import);
                $uses[] = new Node\Stmt\Use_([$useuse]);
            }

            $node->stmts = array_merge($uses, $node->stmts);
        }
        return NULL;
    }


}