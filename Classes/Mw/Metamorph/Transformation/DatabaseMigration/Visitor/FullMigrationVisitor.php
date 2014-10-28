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
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;


class FullMigrationVisitor extends AbstractMigrationVisitor
{



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

            foreach ($node->props as $propertyNode)
            {
                $propertyName = new String($propertyNode->name);
                $columnName = $this->propertyToColumnName($propertyName);

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
                        $foreignPropertyName = $this->columnNameToProperty(new String($propertyConfig['foreign_field']));
                        $annotation->addParameter('mappedBy', "$foreignPropertyName");

                        $this->introduceInversePropertyIfNecessary(
                            $propertyName,
                            $propertyConfig,
                            $foreignPropertyName
                        );
                    }

                    if ($commentString->regexMatch('/@cascade\s+remove/'))
                    {
                        $this->commentHelper->removeAnnotationFromDocComment($comment, '@cascade');
                        $annotation->addParameter('cascade', ['remove']);
                    }

                    $this->commentHelper->addAnnotationToDocComment($comment, $annotation);
                    $this->taskQueue->enqueue(
                        (new AddImportToClassTaskBuilder())
                            ->setTargetClassName($this->currentClass->getFullyQualifiedName())
                            ->setImport('Doctrine\\ORM\\Mapping')
                            ->setNamespaceAlias('ORM')
                            ->buildTask()
                    );
                }

                $innerProperty   = clone $propertyNode;
                $newProperties[] = new Node\Stmt\Property($node->type, [$innerProperty], $node->getAttributes());
            }

            return $newProperties;
        }

        return NULL;
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