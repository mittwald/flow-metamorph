<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Helmich\Scalars\Types\String;
use Mw\Metamorph\Transformation\Task\Builder\AddImportToClassTaskBuilder;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;



/**
 * This visitor looks for static usage of TYPO3 utility classes (like t3lib_div)
 * and replaces these with non-static calls on injected instances of these
 * classes.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\RewriteNodeVisitors
 */
class InjectUtilitiesVisitor extends AbstractVisitor
{



    /**
     * @var Node\Stmt\Namespace_
     */
    private $currentNamespace;


    /**
     * @var Node\Stmt\Class_
     */
    private $currentClass;


//    /**
//     * @var array
//     */
//    private $requiredImports;


    /**
     * @var array
     */
    private $requiredInjections;


//    /**
//     * @var \Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper
//     * @Flow\Inject
//     */
//    protected $importHelper;


    /**
     * @var \Mw\Metamorph\Transformation\Helper\DependencyInjection\InjectionHelper
     * @Flow\Inject
     */
    protected $injectHelper;



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node;
//            $this->requiredImports  = [];
        }
        else if ($node instanceof Node\Stmt\Class_)
        {
            $this->currentClass       = $node;
            $this->requiredInjections = [];
        }
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
//            foreach ($this->requiredImports as $alias => $namespace)
//            {
//                $node = $this->importHelper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
//            }

            $this->currentNamespace = NULL;
//            $this->requiredImports  = [];
//
//            return $node;
        }
        else if ($node instanceof Node\Stmt\Class_)
        {
            foreach ($this->requiredInjections as $name => $class)
            {
                $node = $this->injectHelper->injectDependencyIntoClass($node, $class, $name);

//                $this->requiredImports['Flow'] = 'TYPO3\\Flow\\Annotations';
                $this->taskQueue->enqueue(
                    (new AddImportToClassTaskBuilder())
                        ->setTargetClassName($this->currentClass->namespacedName->toString())
                        ->setImport('TYPO3\\Flow\\Annotations')
                        ->setNamespaceAlias('Flow')
                        ->buildTask()
                );
            }

            $this->currentClass       = NULL;
            $this->requiredInjections = [];

            return $node;
        }
        else if ($node instanceof Node\Expr\StaticCall && $this->isUtilityCall($node))
        {
            $class = $node->class->toString();
            $name  = lcfirst($node->class->getLast());

            $this->requiredInjections[$name] = $class;

            $lookup  = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name);
            $newNode = new Node\Expr\MethodCall(
                $lookup,
                $node->name,
                $node->args
            );

            return $newNode;
        }
        return NULL;
    }



    private function isUtilityCall(Node\Expr\StaticCall $call)
    {
        if (!$call->class instanceof Node\Name)
        {
            return FALSE;
        }

        $name = new String($call->class->toString());
        return
            $name->startWith('TYPO3\\CMS\\Core\\Utility\\') ||
            $name->startWith('Mw\\T3Compat\\Utility') ||
            $name->startWith('t3lib_div');
    }

}