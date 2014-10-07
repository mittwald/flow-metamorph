<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;


class ReplaceMakeInstanceCallsVisitor extends AbstractVisitor
{



    private $imports = [];


    /**
     * @var \Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper
     * @Flow\Inject
     */
    protected $importHelper;



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->imports = [];
        }
    }


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
            foreach ($this->imports as $import)
            {
                $node = $this->importHelper->importNamespaceIntoOtherNamespace($node, $import);
            }

            return $node;
        }
        return NULL;
    }


}