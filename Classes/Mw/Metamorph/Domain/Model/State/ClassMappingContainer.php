<?php
namespace Mw\Metamorph\Domain\Model\State;


use Mw\Metamorph\Domain\Model\MorphConfiguration;

class ClassMappingContainer implements \JsonSerializable
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



    static public function jsonUnserialize(array $data)
    {
        $classes = isset($data['classes']) ? $data['classes'] : [];

        $container                = new ClassMappingContainer();
        $container->reviewed      = isset($data['reviewed']) ? $data['reviewed'] : FALSE;
        $container->classMappings = array_map(
            function ($oldClassName) use ($classes)
            {
                return ClassMapping::jsonUnserialize($classes[$oldClassName], $oldClassName);
            },
            array_keys($classes)
        );

        return $container;
    }



    public function jsonSerialize()
    {
        $serializedMappings = [];

        foreach ($this->classMappings as $classMapping)
        {
            $serializedMappings[$classMapping->getOldClassName()] = $classMapping->jsonSerialize();
        }

        return [
            'reviewed' => $this->reviewed,
            'classes'  => $serializedMappings
        ];
    }



}