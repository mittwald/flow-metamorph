<?php
namespace Mw\Metamorph\Transformation\Analyzer;


use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionDeferred;
use Mw\Metamorph\Domain\Model\Definition\PropertyDefinition;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TYPO3\Flow\Annotations as Flow;


class AnalyzerVisitor extends NodeVisitorAbstract
{



    /**
     * @var ClassDefinitionContainer
     * @Flow\Inject
     */
    protected $classDefinitionContainer;


    /**
     * @var string
     */
    private $currentNamespace = '';


    /**
     * @var ClassDefinition
     */
    private $currentClassDefinition = NULL;


    /**
     * @var ClassMappingContainer
     */
    private $mappingContainer;



    public function __construct(ClassMappingContainer $container)
    {
        $this->mappingContainer = $container;
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node->name->toString();
        }
        elseif ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_)
        {
            $mapping = $this->mappingContainer->getClassMappingByNewClassName(
                $this->currentNamespace . '\\' . $node->name
            );

            $classDef = new ClassDefinition($node->name, $this->currentNamespace);
            $classDef->setClassMapping($mapping);

            if (NULL !== $node->extends && [] !== $node->extends)
            {
                list($class, $namespace) = $this->splitNameIntoClassAndNamespace($node->extends);
                $classDef->setParentClass(new ClassDefinitionDeferred($class, $namespace));
            }

            if ($node->implements)
            {
                foreach ($node->implements as $interface)
                {
                    list($class, $namespace) = $this->splitNameIntoClassAndNamespace($interface);
                    $classDef->addInterface(new ClassDefinitionDeferred($class, $namespace));
                }
            }

            if ($node instanceof Node\Stmt\Class_)
            {
                $classDef->setFact('isAbstract', $node->isAbstract());
                $classDef->setFact('isFinal', $node->isFinal());
            }

            $this->currentClassDefinition = $classDef;
        }
        elseif ($node instanceof Node\Stmt\Property)
        {
            foreach($node->props as $subProp)
            {
                $property = new PropertyDefinition(
                    $subProp->name,
                    $subProp->getDocComment()->getReformattedText()
                );

                $this->currentClassDefinition->addProperty($property);
            }
        }
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_)
        {
            $this->classDefinitionContainer->add($this->currentClassDefinition);
            $this->currentClassDefinition = NULL;
        }
    }



    private function splitNameIntoClassAndNamespace(Node\Name $node)
    {
        $parts = $node->parts;

        $class = array_pop($parts);
        $namespace = implode('\\', $parts);

        return [$class, $namespace];
    }



}