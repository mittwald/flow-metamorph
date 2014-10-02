<?php
namespace Mw\Metamorph\Domain\Model\Definition;


use TYPO3\Flow\Annotations as Flow;


/**
 * @Flow\Scope("singleton")
 */
class ClassDefinitionContainer
{



    private $classDefinitions = [];



    public function add(ClassDefinition $definition)
    {
        $this->classDefinitions[$definition->getFullyQualifiedName()] = $definition;
    }



    /**
     * @param string $fullyQualifiedName
     * @return ClassDefinition
     */
    public function get($fullyQualifiedName)
    {
        $fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
        if (array_key_exists($fullyQualifiedName, $this->classDefinitions))
        {
            return $this->classDefinitions[$fullyQualifiedName];
        }
        return NULL;
    }
}