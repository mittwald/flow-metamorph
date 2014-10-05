<?php
namespace Mw\Metamorph\Domain\Model\Definition;


use TYPO3\Flow\Annotations as Flow;


class ClassDefinitionDeferred extends ClassDefinition
{



    /**
     * @var ClassDefinitionContainer
     * @Flow\Inject
     */
    protected $container;


    /**
     * @var ClassDefinition
     */
    private $realInstance = NULL;



    private function loadRealInstance()
    {
        if (NULL === $this->realInstance)
        {
            $this->realInstance = $this->container->get($this->getFullyQualifiedName());
        }
    }



    public function getParentClass()
    {
        $this->loadRealInstance();
        return $this->realInstance ? $this->realInstance->getParentClass() : NULL;
    }



    public function getInterfaces()
    {
        $this->loadRealInstance();
        return $this->realInstance ? $this->realInstance->getInterfaces() : [];
    }



    public function getFact($name)
    {
        $this->loadRealInstance();
        return $this->realInstance ? $this->realInstance->getFact($name) : NULL;
    }



    public function getClassMapping()
    {
        $this->loadRealInstance();
        return $this->realInstance ? $this->realInstance->getClassMapping() : NULL;
    }



}