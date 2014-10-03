<?php
namespace Mw\Metamorph\Domain\Model\State;


class ClassMappingContainer
{



    use Reviewable;



    /** @var ClassMapping[] */
    protected $classMappings = [];



    public function getClassMappings()
    {
        return $this->classMappings;
    }



    public function hasClassMapping($oldClassName)
    {
        return NULL !== $this->getClassMapping($oldClassName);
    }



    public function addClassMapping(ClassMapping $classMapping)
    {
        if (FALSE === $this->hasClassMapping($classMapping->getOldClassName()))
        {
            $this->reviewed        = FALSE;
            $this->classMappings[] = $classMapping;
        }
    }



    public function getClassMapping($oldClassName)
    {
        foreach ($this->classMappings as $classMapping)
        {
            if ($classMapping->getOldClassName() === $oldClassName)
            {
                return $classMapping;
            }
        }
        return NULL;
    }



}