<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Tca;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;


class TcaLoaderVisitor extends NodeVisitorAbstract
{



    private $tca;



    public function __construct(Tca $tca)
    {
        $this->tca = $tca;
    }



    public function enterNode(Node $node)
    {
        if ($this->isTcaAssignment($node))
        {
            echo "FOUND TCA ASSIGNMENT!\n";
        }
    }



    private function isTcaAssignment(Node $node)
    {
        if (!$node instanceof Node\Expr\Assign)
        {
            return FALSE;
        }

        $left = $node->var;
        if ($left instanceof Node\Expr\Variable && $left->name === 'TCA')
        {
            return TRUE;
        }

        if ($left instanceof Node\Expr\ArrayDimFetch)
        {
            list($var, $dim) = $this->getVarAndKeysFromArrayFetch($left);

            /** @var Node\Expr\Variable $var */
            if ($var->name === 'TCA')
            {
                print_r($node);
                return TRUE;
            }
        }
    }



    private function getVarAndKeysFromArrayFetch(Node\Expr\ArrayDimFetch $node)
    {
        $left = $node;
        while (!$left instanceof Node\Expr\Variable)
        {
            $left = $left->var;
        }

        $right = $node;
        while (!$right instanceof String)

        return [$left, NULL];
    }


}