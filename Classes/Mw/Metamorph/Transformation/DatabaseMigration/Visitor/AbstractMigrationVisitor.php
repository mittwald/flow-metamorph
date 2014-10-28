<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;


use Helmich\Scalars\Types\ArrayList;
use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Task\Builder\AddImportToClassTaskBuilder;
use Mw\Metamorph\Transformation\Task\Builder\AddPropertyToClassTaskBuilder;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\NodeVisitorAbstract;


class AbstractMigrationVisitor extends NodeVisitorAbstract
{



    /**
     * @var Tca
     */
    protected $tca;


    /**
     * @var ClassDefinitionContainer
     * @Flow\Inject
     */
    protected $classDefinitionContainer;


    /**
     * @var DocCommentModifier
     * @Flow\Inject
     */
    protected $commentHelper;


    /**
     * @var ClassDefinition
     */
    protected $currentClass;


    /**
     * @var array
     */
    protected $currentTca;


    /** @var string */
    protected $currentTable;


    /**
     * @var TaskQueue
     */
    protected $taskQueue;



    public function __construct(Tca $tca, TaskQueue $taskQueue)
    {
        $this->tca       = $tca;
        $this->taskQueue = $taskQueue;
    }



    protected function getTcaForClass(String $newClassName, String $oldClassName, array &$tca, &$tableName)
    {
        $possibleNames = $this->getPossibleTableNamesForClass($newClassName, $oldClassName);
        foreach ($possibleNames as $name)
        {
            if (isset($this->tca[$name]))
            {
                $tca       = $this->tca[$name];
                $tableName = $name;
            }
        }
    }



    protected function getClassForTable($tableName)
    {
        $filterFunction = function (ClassDefinition $definition) use ($tableName)
        {
            $possibleTableNames = $this->getPossibleTableNamesForClass(
                new String($definition->getFullyQualifiedName()),
                new String($definition->getClassMapping()->getOldClassName())
            );

            return $possibleTableNames->contains($tableName);
        };

        $classDefinitions = $this->classDefinitionContainer->findByFilter($filterFunction);
        return (count($classDefinitions) > 0) ? $classDefinitions[0] : NULL;
    }



    protected function isTcaColumnManyToOneRelation(array $configuration)
    {
        if ($configuration['type'] === 'select')
        {
            if (!isset($configuration['maxitems']) || $configuration['maxitems'] == 1)
            {
                return TRUE;
            }
        }
        elseif ($configuration['type'] === 'group' && $configuration['internal_type'] === 'db')
        {
            if (isset($configuration['maxitems']) && $configuration['maxitems'] == 1)
            {
                return TRUE;
            }
        }
        return FALSE;
    }



    protected function isTcaColumnOneToManyRelation(array $configuration)
    {
        switch ($configuration['type'])
        {
            case 'select':
                if (isset($configuration['maxitems']) && $configuration['maxitems'] > 1)
                {
                    return TRUE;
                }
                break;

            case 'group':
                if ($configuration['internal_type'] === 'db' && (!isset($configuration['maxitems']) || $configuration['maxitems'] > 1))
                {
                    return TRUE;
                }
                break;

            case 'inline':
                if (!isset($configuration['maxitems']) || $configuration['maxitems'] > 1)
                {
                    return TRUE;
                }
                break;
        }
        return FALSE;
    }



    protected function isTcaColumnOneToOneRelation(array $configuration)
    {
        switch ($configuration['type'])
        {
            case 'inline':
                if (isset($configuration['maxitems']) && $configuration['maxitems'] == 1)
                {
                    return TRUE;
                }
                break;
        }
        return FALSE;
    }



    protected function isTcaColumnManyToManyRelation(array $configuration)
    {
        switch ($configuration['type'])
        {
            case 'select':
            case 'group':
                if (isset($configuration['MM']))
                {
                    return TRUE;
                }
                break;
        }
        return FALSE;
    }



    protected function propertyToColumnName(String $propertyName)
    {
        return $propertyName->regexReplace(',([A-Z]),', '_$1')->toLower()->strip('_');
    }



    protected function columnNameToProperty(String $columnName)
    {
        return $columnName
            ->split('_')
            ->mapWithKey(function ($index, String $part) { return $index === 0 ? $part : $part->toCamelCase(); })
            ->join('');
    }



    /**
     * @param \Helmich\Scalars\Types\String $newClassName
     * @param \Helmich\Scalars\Types\String $oldClassName
     * @return ArrayList
     */
    protected function getPossibleTableNamesForClass(String $newClassName, String $oldClassName)
    {
        return new ArrayList(
            [
                $newClassName->toLower()->replace('\\', '_'),
                $newClassName->toLower()->split('\\')->set(0, 'tx')->join('_'),
                $oldClassName->toLower()->replace('\\', '_'),
                $oldClassName->toLower()->split('\\')->set(0, 'tx')->join('_')
            ]
        );
    }



    /**
     * @param $propertyName
     * @param $propertyConfig
     * @param $foreignPropertyName
     */
    protected function introduceInversePropertyIfNecessary($propertyName, $propertyConfig, $foreignPropertyName)
    {
        $targetClass = $this->getClassForTable($propertyConfig['foreign_table']);
        if (!$targetClass->hasProperty($foreignPropertyName))
        {
            $inverseAnnotation = new AnnotationRenderer('ORM', 'ManyToOne');
            $inverseAnnotation->addParameter('inversedBy', "{$propertyName}");

            $this->taskQueue->enqueue(
                (new AddPropertyToClassTaskBuilder())
                    ->setTargetClassName($targetClass->getFullyQualifiedName())
                    ->setPropertyName("$foreignPropertyName")
                    ->setPropertyType('\\' . $this->currentClass->getFullyQualifiedName())
                    ->setProtected()
                    ->addAnnotation($inverseAnnotation->render())
                    ->buildTask()
            );

            $this->taskQueue->enqueue(
                (new AddImportToClassTaskBuilder())
                    ->setTargetClassName($targetClass->getFullyQualifiedName())
                    ->setImport('Doctrine\\ORM\\Mapping')
                    ->setNamespaceAlias('ORM')
                    ->buildTask()
            );
        }
    }
}