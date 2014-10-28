<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;


use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\NodeVisitorAbstract;


/**
 * Database migration visitor that adds Doctrine annotations that allow you
 * to re-use your old database scheme.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Visitor
 */
class CompatibleMigrationVisitor extends NodeVisitorAbstract
{



    /** @var Tca */
    private $tca;


    /** @var TaskQueue */
    private $taskQueue;



    public function __construct(Tca $tca, TaskQueue $queue)
    {
        $this->tca       = $tca;
        $this->taskQueue = $queue;
    }


}