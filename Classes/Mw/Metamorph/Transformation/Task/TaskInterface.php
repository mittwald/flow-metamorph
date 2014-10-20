<?php
namespace Mw\Metamorph\Transformation\Task;


use Mw\Metamorph\Domain\Model\MorphConfiguration;


interface TaskInterface
{



    public function execute(MorphConfiguration $configuration, TaskQueue $queue);



    public function toString();



    public function getHash();

}