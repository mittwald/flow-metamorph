<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;


use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;


class FullMigrationVisitor extends NodeVisitorAbstract
{



    /**
     * @var Tca
     */
    private $tca;



    public function __construct(Tca $tca)
    {
        $this->tca = $tca;
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_)
        {
            echo "FOUND CLASS: " . $node->namespacedName->toString() . "\n";
        }
    }



    private function getPossibleTableNamesForClass($className)
    {
        $possibleNames = [];

        $possibleNames[] = str_replace('\\', '_', strtolower($className));
    }



}