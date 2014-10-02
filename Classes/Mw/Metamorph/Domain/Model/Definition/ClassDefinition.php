<?php
namespace Mw\Metamorph\Domain\Model\Definition;


class ClassDefinition
{



    private $name;


    private $namespace;


    /** @var ClassDefinition */
    private $parentClass = NULL;


    /** @var ClassDefinition[] */
    private $interfaces = [];



    public function __construct($name, $namespace)
    {
        $this->name      = $name;
        $this->namespace = $namespace;
    }



    public function setParentClass(ClassDefinition $parentClass)
    {
        $this->parentClass = $parentClass;
    }



    public function addInterface(ClassDefinition $interface)
    {
        $this->interfaces[] = $interface;
    }



    public function getFullyQualifiedName()
    {
        return ltrim($this->namespace . '\\' . $this->name, '\\');
    }



    public function getParentClass()
    {
        return $this->parentClass;
    }



    public function getInterfaces()
    {
        return $this->interfaces;
    }



    public function doesInherit($fullyQualifiedName)
    {
        if ($this->getParentClass() !== NULL)
        {
            if ($fullyQualifiedName == $this->getParentClass()->getFullyQualifiedName())
            {
                return TRUE;
            }
            else
            {
                return $this->getParentClass()->doesInherit($fullyQualifiedName);
            }
        }
        return FALSE;
    }



    public function doesImplement($fullyQualifiedName)
    {
        foreach ($this->interfaces as $interface)
        {
            if ($fullyQualifiedName === $interface->getFullyQualifiedName())
            {
                return TRUE;
            }
            if ($interface->doesInherit($fullyQualifiedName))
            {
                return TRUE;
            }
        }

        if (NULL !== $this->parentClass && $this->parentClass->doesImplement($fullyQualifiedName))
        {
            return TRUE;
        }

        return FALSE;
    }

}