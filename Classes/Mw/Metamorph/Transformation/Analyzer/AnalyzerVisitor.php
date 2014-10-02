<?php
namespace Mw\Metamorph\Transformation\Analyzer;


use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionDeferred;
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



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node->name;
        }
        elseif ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_)
        {
            $classDef = new ClassDefinition($node->name, $this->currentNamespace);

            if (NULL !== $node->extends && [] !== $node->extends)
            {
                list($class, $namespace) = $this->splitNameIntoClassAndNamespace($node->extends);
                $classDef->setParentClass(new ClassDefinitionDeferred($class, $namespace));
                echo "INHERIT: " . $classDef->getFullyQualifiedName() . ' --> ' . $namespace . '\\' . $class . "\n";
            }

            if ($node->implements)
            {
                foreach ($node->implements as $interface)
                {
                    list($class, $namespace) = $this->splitNameIntoClassAndNamespace($interface);
                    $classDef->addInterface(new ClassDefinitionDeferred($class, $namespace));
                }
            }

            $this->currentClassDefinition = $classDef;
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

        $class     = array_pop($parts);
        $namespace = implode('\\', $parts);

        return [$class, $namespace];
    }



}