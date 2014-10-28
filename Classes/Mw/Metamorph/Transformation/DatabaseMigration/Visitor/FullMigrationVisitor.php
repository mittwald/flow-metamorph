<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;


use Helmich\Scalars\Types\String;
use Mw\Metamorph\Transformation\Task\Builder\AddImportToClassTaskBuilder;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;


class FullMigrationVisitor extends AbstractMigrationVisitor
{



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

            $comment = $this->getOrCreateNodeDocComment($node);
            $commentString = new String($comment->getText());

            foreach ($node->props as $propertyNode)
            {
                $propertyName = new String($propertyNode->name);
                $columnName   = $this->mappingHelper->propertyToColumnName($propertyName);

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
                        $foreignPropertyName = $this->mappingHelper->columnNameToProperty(
                            new String($propertyConfig['foreign_field'])
                        );
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






}