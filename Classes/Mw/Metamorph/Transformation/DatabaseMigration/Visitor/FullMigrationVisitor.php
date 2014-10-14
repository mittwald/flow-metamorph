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
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;


class FullMigrationVisitor extends NodeVisitorAbstract
{



    /**
     * @var Tca
     */
    private $tca;



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
    private $currentClass;


    /**
     * @var array
     */
    private $currentTca;


    /** @var string */
    private $currentTable;


    /**
     * @var \SplPriorityQueue
     */
    private $taskQueue;



    public function __construct(Tca $tca, \SplPriorityQueue $taskQueue)
    {
        $this->tca       = $tca;
        $this->taskQueue = $taskQueue;
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_)
        {
            $newClassName    = $node->namespacedName->toString();
            $classDefinition = $this->classDefinitionContainer->get($newClassName);
            $classMapping    = $classDefinition->getClassMapping();

            if (NULL === $classMapping)
            {
                throw new \Exception('No class mapping found for class ' . $newClassName);
            }

            $this->currentTca = [];
            $this->getTcaForClass(
                new String($newClassName),
                new String($classMapping->getOldClassName()),
                $this->currentTca,
                $this->currentTable
            );

            $this->currentClass = $classDefinition;

            if ($this->currentTable === NULL && $this->currentClass->getFact('isAbstract') === FALSE)
            {
                $comment = $node->getDocComment();
                if (NULL != $comment)
                {
                    $this->commentHelper->removeAnnotationFromDocComment($comment, '@Flow\\Entity');
                    $this->commentHelper->removeAnnotationFromDocComment($comment, '@Flow\\ValueObject');
                }
                return $node;
            }
        }

        return NULL;
    }



    private function getTcaForClass(String $newClassName, String $oldClassName, array &$tca, &$tableName)
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



    private function getClassForTable($tableName)
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



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_)
        {
            $this->currentClass = NULL;
            $this->currentTca   = NULL;
            $this->currentTable = NULL;
        }
        elseif ($node instanceof Node\Stmt\Property && $this->currentTable !== NULL)
        {
            if (NULL === $this->currentTca)
            {
                return NULL;
            }

            $newProperties = [];

            $comment = $node->getDocComment();
            if (NULL === $comment)
            {
                $comments   = $node->getAttribute('comments', []);
                $comments[] = $comment = new Doc("/**\n */");

                $node->setAttribute('comments', $comments);
            }
            $commentString = new String($comment->getText());

            foreach ($node->props as $realProperty)
            {
                $columnName = $this->propertyToColumnName(new String($realProperty->name));

                if (!isset($this->currentTca['columns']["$columnName"]))
                {
                    return NULL;
                }

                $propertyConfig = $this->currentTca['columns']["$columnName"]['config'];
                $annotation     = $this->getAnnotationRendererForPropertyConfiguration($propertyConfig);

                if (NULL !== $annotation)
                {
                    if ($this->isTcaColumnManyToOneRelation($propertyConfig) && isset($propertyConfig['foreign_table']))
                    {
                        $foreignTable = $propertyConfig['foreign_table'];
                        $inverse      = NULL;

                        foreach ((array)$this->tca[$foreignTable]['columns'] as $foreignColumnName => $config)
                        {
                            if (
                                isset($config['config']['foreign_field']) &&
                                $config['config']['foreign_field'] == $columnName &&
                                $config['config']['foreign_table'] == $this->currentTable
                            )
                            {
                                $inverse = $foreignColumnName;
                                break;
                            }
                        }

                        if (NULL !== $inverse)
                        {
                            $annotation->addParameter('inversedBy', $inverse);
                        }
                    }

                    if ($this->isTcaColumnOneToManyRelation($propertyConfig) && isset($propertyConfig['foreign_field']))
                    {
                        $property = $this->columnNameToProperty(new String($propertyConfig['foreign_field']));
                        $annotation->addParameter('mappedBy', "$property");

                        $targetClass = $this->getClassForTable($propertyConfig['foreign_table']);
                        if (!$targetClass->hasProperty($property))
                        {
                            $inverseAnnotation = new AnnotationRenderer('ORM', 'ManyToOne');
                            $inverseAnnotation->addParameter('inversedBy', $realProperty->name);

                            $this->taskQueue->insert(
                                (new AddPropertyToClassTaskBuilder())
                                    ->setTargetClassName($targetClass->getFullyQualifiedName())
                                    ->setPropertyName("$property")
                                    ->setPropertyType('\\' . $this->currentClass->getFullyQualifiedName())
                                    ->setProtected()
                                    ->addAnnotation($inverseAnnotation->render())
                                    ->buildTask(),
                                0
                            );

                            $this->taskQueue->insert(
                                (new AddImportToClassTaskBuilder())
                                    ->setTargetClassName($targetClass->getFullyQualifiedName())
                                    ->setImportNamespace('Doctrine\\ORM\\Mapping')
                                    ->setNamespaceAlias('ORM')
                                    ->buildTask(),
                                0
                            );
                        }
                    }

                    if ($commentString->regexMatch('/@cascade\s+remove/'))
                    {
                        $this->commentHelper->removeAnnotationFromDocComment($comment, '@cascade');
                        $annotation->addParameter('cascade', ['remove']);
                    }

                    $this->commentHelper->addAnnotationToDocComment($comment, $annotation);
                    $this->taskQueue->insert(
                        (new AddImportToClassTaskBuilder())
                            ->setTargetClassName($this->currentClass->getFullyQualifiedName())
                            ->setImportNamespace('Doctrine\\ORM\\Mapping')
                            ->setNamespaceAlias('ORM')
                            ->buildTask(),
                        0
                    );
                }

                $innerProperty   = clone $realProperty;
                $newProperties[] = new Node\Stmt\Property($node->type, [$innerProperty], $node->getAttributes());
            }

            return $newProperties;
        }

        return NULL;
    }



    private function isTcaColumnManyToOneRelation(array $configuration)
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



    private function isTcaColumnOneToManyRelation(array $configuration)
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



    private function isTcaColumnOneToOneRelation(array $configuration)
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



    private function isTcaColumnManyToManyRelation(array $configuration)
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



    private function propertyToColumnName(String $propertyName)
    {
        return $propertyName->regexReplace(',([A-Z]),', '_$1')->toLower()->strip('_');
    }



    private function columnNameToProperty(String $columnName)
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
    private function getPossibleTableNamesForClass(String $newClassName, String $oldClassName)
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
     * @param $propertyConfig
     * @return AnnotationRenderer
     */
    private function getAnnotationRendererForPropertyConfiguration($propertyConfig)
    {
        if ($this->isTcaColumnManyToManyRelation($propertyConfig))
        {
            return new AnnotationRenderer('ORM', 'ManyToMany');
        }
        else if ($this->isTcaColumnManyToOneRelation($propertyConfig))
        {
            return new AnnotationRenderer('ORM', 'ManyToOne');
        }
        else if ($this->isTcaColumnOneToManyRelation($propertyConfig))
        {
            return new AnnotationRenderer('ORM', 'OneToMany');
        }
        else if ($this->isTcaColumnOneToOneRelation($propertyConfig))
        {
            return new AnnotationRenderer('ORM', 'OneToOne');
        }
        return NULL;
    }



}