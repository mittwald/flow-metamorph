<?php
namespace Mw\Metamorph\Persistence\Mapping\State;


use Mw\Metamorph\Domain\Model\State\ClassMapping;


class ClassMappingProxy extends ClassMapping
{



    public function __construct($oldClassName, array $data)
    {
        $this->sourceFile   = $data['source'];
        $this->oldClassName = $oldClassName;
        $this->newClassName = $data['newClassname'];
        $this->package      = $data['package'];
        $this->action       = $data['action'];

        if (isset($data['target']))
        {
            $this->targetFile = $data['target'];
        }
    }



}