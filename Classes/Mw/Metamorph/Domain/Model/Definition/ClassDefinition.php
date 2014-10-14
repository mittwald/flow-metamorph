<?php
namespace Mw\Metamorph\Domain\Model\Definition;


use Mw\Metamorph\Domain\Model\State\ClassMapping;

class ClassDefinition
{



    private $name;


    private $namespace;


    /** @var ClassDefinition */
    private $parentClass = NULL;


    /** @var ClassDefinition[] */
    private $interfaces = [];


    /** @var array */
    private $facts = [];


    /** @var PropertyDefinition[] */
    private $properties = [];


    /** @var ClassMapping */
    private $classMapping;



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
        foreach ($this->getInterfaces() as $interface)
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

        if (NULL !== $this->getParentClass() && $this->getParentClass()->doesImplement($fullyQualifiedName))
        {
            return TRUE;
        }

        return FALSE;
    }



    public function getFact($name)
    {
        return array_key_exists($name, $this->facts) ? $this->facts[$name] : NULL;
    }



    public function setFact($name, $fact)
    {
        $this->facts[$name] = $fact;
    }



    /**
     * @return ClassMapping
     */
    public function getClassMapping()
    {
        return $this->classMapping;
    }



    /**
     * @param ClassMapping $classMapping
     */
    public function setClassMapping(ClassMapping $classMapping)
    {
        $this->classMapping = $classMapping;
    }



    /**
     * @param PropertyDefinition $propertyDefinition
     * @return void
     */
    public function addProperty(PropertyDefinition $propertyDefinition)
    {
        $this->properties[$propertyDefinition->getName()] = $propertyDefinition;
    }



    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }



    /**
     * @param string $name
     * @return PropertyDefinition
     */
    public function getProperty($name)
    {
        return $this->hasProperty($name) ? $this->properties[$name] : NULL;
    }



}