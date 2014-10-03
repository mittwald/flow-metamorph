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
    }


}