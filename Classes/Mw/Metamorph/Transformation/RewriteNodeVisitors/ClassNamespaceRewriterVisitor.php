<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Node;


class ClassNamespaceRewriterVisitor extends AbstractVisitor
{



    /**
     * @var \PhpParser\Node\Stmt\Namespace_
     */
    protected $currentNamespaceNode = NULL;


    /**
     * @var \PhpParser\Node\Stmt\Class_
     */
    protected $currentClassNode = NULL;


    protected $newNamespace = NULL;


    protected $imports = [];


    protected $definedClasses = [];



    public function beforeTraverse(array $nodes)
    {
        $this->imports              = [];
        $this->currentNamespaceNode = NULL;
        $this->newNamespace         = NULL;
    }



    public function enterNode(Node $node)
    {
        if ($node->getDocComment())
        {
            $text = $node->getDocComment()->getText();

            foreach ($this->classMap['classes'] as $old => $config)
            {
                $new = $config['newClassname'];
                if (strpos($text, $old) !== FALSE)
                {
                    $text = str_replace($old, $new, $text);
                }
            }

            $node->getDocComment()->setText($text);
        }

        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespaceNode = $node;
        }
        elseif ($node instanceof Node\Stmt\Class_)
        {
            $this->currentClassNode = $node;
            $oldName                = $node->namespacedName->toString();

            if (isset($this->classMap['classes'][$oldName]))
            {
                list($namespace, $relativeClassName) = $this->getNamespaceAndRelativeNameForOldClass($oldName);

                $node->name           = new Node\Name($relativeClassName);
                $node->namespacedName = new Node\Name($namespace . '\\' . $relativeClassName);

                $this->newNamespace = new Node\Name($namespace);
                return $node;
            }
        }
        elseif ($node instanceof Node\Name)
        {
            $oldName = $node->toString();
            if (isset($this->classMap['classes'][$oldName]))
            {
                list($namespace, $relativeClassName) = $this->getNamespaceAndRelativeNameForOldClass($oldName);
                $fqcn = $namespace . '\\' . $relativeClassName;

                if ($this->currentClassNode === NULL || $this->currentClassNode->namespacedName->toString() != $fqcn)
                {
                    $this->imports[$fqcn] = new Node\Name($fqcn);
                }
                return new Node\Name($relativeClassName);
            }
        }
        elseif ($node instanceof Node\Scalar\String)
        {
            $text = $node->value;
            foreach ($this->classMap['classes'] as $old => $config)
            {
                $new = $config['newClassname'];
                if (strpos($text, $old) !== FALSE)
                {
                    $text = str_replace($old, $new, $text);
                }
            }
            $node->value = $text;
        }
        return NULL;
    }



    private function getNamespaceAndRelativeNameForOldClass($oldClass)
    {
        $newName           = $this->classMap['classes'][$oldClass]['newClassname'];
        $newNameComponents = explode('\\', $newName);

        $relativeClassName = array_pop($newNameComponents);
        $namespace         = implode('\\', $newNameComponents);

        return [$namespace, $relativeClassName];
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_)
        {
            $this->currentClassNode = NULL;
            if ($this->currentNamespaceNode === NULL && $this->newNamespace !== NULL)
            {
                $uses = $this->getUseStatements();

                $namespaceNode = new Node\Stmt\Namespace_($this->newNamespace, array_merge($uses, [$node]));
                return $namespaceNode;
            }
        }
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespaceNode = NULL;
            if ($this->newNamespace !== NULL)
            {
                $uses = $this->getUseStatements();

                $node->name  = $this->newNamespace;
                $node->stmts = array_merge($uses, $node->stmts);

                $this->newNamespace = NULL;
                return $node;
            }
        }
        return NULL;
    }



    private function getUseStatements()
    {
        $uses = [];

        foreach ($this->imports as $fqcn => $name)
        {
            $useuse = new Node\Stmt\UseUse($name);
            $uses[] = new Node\Stmt\Use_([$useuse]);
        }

        return $uses;
    }



}