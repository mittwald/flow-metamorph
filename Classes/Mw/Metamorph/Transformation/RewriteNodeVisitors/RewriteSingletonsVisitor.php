<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;


class RewriteSingletonsVisitor extends AbstractVisitor
{



    /**
     * @var ClassDefinitionContainer
     * @Flow\Inject
     */
    protected $classDefinitionContainer;



    /**
     * @var ImportHelper
     * @Flow\Inject
     */
    protected $importHelper;


    /**
     * @var Node\Stmt\Namespace_
     */
    private $currentNamespace;


    /**
     * @var DocCommentModifier
     * @Flow\Inject
     */
    protected $commentModifier;


    /**
     * @var array
     */
    private $requiredImports;



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node;
            $this->requiredImports  = [];
        }
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_ && count($this->requiredImports) > 0)
        {
            foreach ($this->requiredImports as $alias => $namespace)
            {
                $node = $this->importHelper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
            }
            return $node;
        }
        if ($node instanceof Node\Stmt\Class_)
        {
            /** @noinspection PhpUndefinedFieldInspection */
            $name       = $node->namespacedName->toString();
            $definition = $this->classDefinitionContainer->get($name);

            if ($this->isSingleton($definition))
            {
                $implementsList = $node->implements;
                foreach ($implementsList as $key => $implements)
                {
                    if ($implements->toString() === 't3lib_Singleton' || $implements->toString(
                        ) === 'TYPO3\\CMS\\Core\\SingletonInterface'
                    )
                    {
                        unset($implementsList[$key]);
                    }
                }
                $node->implements = array_values($implementsList);

                $comment = $node->getDocComment();
                if (NULL === $comment)
                {
                    $comments   = $node->getAttribute('comments', []);
                    $comments[] = $comment = new Doc("/**\n */");

                    $node->setAttribute('comments', $comments);
                }
                $annotation = new AnnotationRenderer('Flow', 'Scope');
                $annotation->setArgument('singleton');

                $this->commentModifier->addAnnotationToDocComment($comment, $annotation);
                $this->requiredImports['Flow'] = 'TYPO3\\Flow\\Annotations';

                return $node;
            }
        }
        return NULL;
    }



    private function isSingleton(ClassDefinition $classDefinition)
    {
        return
            $classDefinition->doesImplement('t3lib_Singleton') ||
            $classDefinition->doesImplement('TYPO3\\CMS\\Core\\SingletonInterface') ||
            $classDefinition->doesInherit('TYPO3\\CMS\\Extbase\\Persistence\\Repository');
    }



}