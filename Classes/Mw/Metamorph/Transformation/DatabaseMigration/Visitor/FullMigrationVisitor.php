<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Visitor;


use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\AccountRepository;


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
     * @var ImportHelper
     * @Flow\Inject
     */
    protected $namespaceHelper;


    /**
     * @var ClassDefinition
     */
    private $currentClass;


    /**
     * @var array
     */
    private $currentTca;


    /**
     * @var Node\Stmt\Namespace_
     */
    private $currentNamespace;


    /**
     * @var array
     */
    private $requiredImports;



    public function __construct(Tca $tca)
    {
        $this->tca = $tca;
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node;
            $this->requiredImports  = [];
        }
        else if ($node instanceof Node\Stmt\Class_)
        {
            $newClassName    = $node->namespacedName->toString();
            $classDefinition = $this->classDefinitionContainer->get($newClassName);
            $classMapping    = $classDefinition->getClassMapping();

            if (NULL === $classMapping)
            {
                throw new \Exception('No class mapping found for class ' . $newClassName);
            }

            $possibleNames = $this->getPossibleTableNamesForClass(
                new String($newClassName),
                new String($classMapping->getOldClassName())
            );

            $this->currentClass = $classDefinition;
            foreach ($possibleNames as $name)
            {
                if (isset($this->tca[$name]))
                {
                    $this->currentTca = $this->tca[$name];
                }
            }
        }

    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_ && count($this->requiredImports) > 0)
        {
            foreach ($this->requiredImports as $alias => $namespace)
            {
                $node = $this->namespaceHelper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
            }
            return $node;
        }
        elseif ($node instanceof Node\Stmt\Class_)
        {
            $this->currentClass = NULL;
            $this->currentTca   = NULL;
        }
        elseif ($node instanceof Node\Stmt\Property)
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
                $columnName     = $this->propertyToColumnName(new String($realProperty->name));
                $propertyConfig = NULL;

                if (isset($this->currentTca['columns']["$columnName"]))
                {
                    $propertyConfig = $this->currentTca['columns']["$columnName"];
                }
                else
                {
                    return NULL;
                }

                $annotation = NULL;
                if ($propertyConfig['config']['type'] === 'select')
                {
                    if (!isset($propertyConfig['config']['maxitems']) || $propertyConfig['config']['maxitems'] === 1)
                    {
                        $annotation = new AnnotationRenderer('ORM', 'ManyToOne');
                    }
                    elseif (isset($propertyConfig['config']['MM']))
                    {
                        $annotation = new AnnotationRenderer('ORM', 'ManyToMany');
                    }
                }
                else if ($propertyConfig['config']['type'] === 'inline')
                {
                    if (isset($propertyConfig['config']['maxitems']) && $propertyConfig['config']['maxitems'] === 1)
                    {
                        $annotation = new AnnotationRenderer('ORM', 'OneToOne');
                    }
                    else
                    {
                        $annotation = new AnnotationRenderer('ORM', 'OneToMany');
                    }

                    if (isset($propertyConfig['config']['foreign_field']))
                    {
                        $property = $this->columnNameToProperty(new String($propertyConfig['config']['foreign_field']));
                        $annotation->addParameter('mappedBy', "$property");
                    }

                    if ($commentString->regexMatch('/@cascade\s+remove/'))
                    {
                        $this->commentHelper->removeAnnotationFromDocComment($comment, '@cascade');
                        $annotation->addParameter('cascade', ['remove']);
                    }
                }

                if (NULL !== $annotation)
                {
                    $this->commentHelper->addAnnotationToDocComment($comment, $annotation);
                    $this->requiredImports['ORM'] = 'Doctrine\\ORM\\Mapping';
                }

                $innerProperty   = clone $realProperty;
                $newProperties[] = new Node\Stmt\Property($node->type, [$innerProperty], $node->getAttributes());
            }

            return $newProperties;
        }

        return NULL;
    }



    private function propertyToColumnName(String $propertyName)
    {
        return $propertyName->regexReplace(',[A-Z],', '_$1')->toLower()->strip('_');
    }



    private function columnNameToProperty(String $columnName)
    {
        return $columnName
            ->split('_')
            ->mapWithKey(function ($index, String $part) { return $index === 0 ? $part : $part->toCamelCase(); })
            ->join('');
    }



    private function getPossibleTableNamesForClass(String $newClassName, String $oldClassName)
    {
        return [
            $newClassName->toLower()->replace('\\', '_'),
            $newClassName->toLower()->split('\\')->set(0, 'tx')->join('_'),
            $oldClassName->toLower()->replace('\\', '_'),
            $oldClassName->toLower()->split('\\')->set(0, 'tx')->join('_')
        ];
    }



}