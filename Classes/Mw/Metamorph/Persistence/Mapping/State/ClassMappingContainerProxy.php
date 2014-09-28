<?php
namespace Mw\Metamorph\Persistence\Mapping\State;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use TYPO3\Flow\Annotations as Flow;


class ClassMappingContainerProxy extends ClassMappingContainer
{



    use YamlStorable;



    public function __construct(MorphConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }



    public function initializeObject()
    {
        $this->initializeWorkingDirectory($this->configuration->getName());

        $data          = $this->readYamlFile('ClassMap', FALSE);
        $classMappings = [];

        foreach ($this->getArrayProperty($data, 'classes', []) as $oldClassName => $classData)
        {
            $classMappings[] = new ClassMappingProxy($oldClassName, $classData);
        }

        $this->classMappings = $classMappings;
        $this->reviewed      = $this->getArrayProperty($data, 'reviewed', FALSE);
    }



}