<?php
namespace Mw\Metamorph\Transformation\Helper;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;


class ClosureVisitor extends NodeVisitorAbstract
{



    private $onEnter = NULL;


    private $onLeave = NULL;



    public function setOnEnter(callable $callback)
    {
        $this->onEnter = $callback;
    }



    public function setOnLeave(callable $callback)
    {
        $this->onLeave = $callback;
    }



    public function enterNode(Node $node)
    {
        if (is_callable($this->onEnter))
        {
            return call_user_func($this->onEnter, $node);
        }
        return NULL;
    }



    public function leaveNode(Node $node)
    {
        if (is_callable($this->onLeave))
        {
            return call_user_func($this->onLeave, $node);
        }
        return NULL;
    }



}