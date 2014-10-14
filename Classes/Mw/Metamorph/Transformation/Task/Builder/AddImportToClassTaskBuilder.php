<?php
namespace Mw\Metamorph\Transformation\Task\Builder;


use Mw\Metamorph\Transformation\Task\AddImportToClassTask;

class AddImportToClassTaskBuilder
{



    private $class;


    private $namespace;


    private $alias = NULL;



    /**
     * @param string $targetClassName
     * @return self
     */
    public function setTargetClassName($targetClassName)
    {
        $this->class = $targetClassName;
        return $this;
    }



    public function setImportNamespace($importNamespace)
    {
        $this->namespace = $importNamespace;
        return $this;
    }



    public function setNamespaceAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }



    public function buildTask()
    {
        return new AddImportToClassTask(
            $this->class,
            $this->namespace,
            $this->alias
        );
    }

} 