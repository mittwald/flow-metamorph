<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use PhpParser\NodeVisitorAbstract;


class AbstractVisitor extends NodeVisitorAbstract
{



    protected $settings;


    /** @var ClassMappingContainer */
    protected $classMap;


    /** @var \SplPriorityQueue */
    protected $taskQueue;



    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }



    public function setClassMap(ClassMappingContainer $classMap)
    {
        $this->classMap = $classMap;
    }



    public function setDeferredTaskQueue(\SplPriorityQueue $queue)
    {
        $this->taskQueue = $queue;
    }

}