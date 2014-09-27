<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Node;

class ReplaceAnnotationsVisitor extends AbstractVisitor
{



    /** @var Node\Stmt\Namespace_ */
    private $currentNamespace;


    /** @var array */
    private $annotationMapping = [
        '@inject'       => '@Flow\\Inject',
        '@validate'     => '@Flow\\Validate',
        '@dontvalidate' => '@Flow\\IgnoreValidation'
    ];


    private $namespaceMappings = [
        'Flow' => 'TYPO3\\Flow\\Annotations',
        'ORM'  => 'Doctrine\\ORM\\Mapping'
    ];


    private $requiredNamespaceImports = [];



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace = $node;
        }
    }



    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {

        }
        else
        {
            foreach ($this->annotationMapping as $oldAnnotation => $newAnnotation)
            {
                $annotationNamespace = explode('\\', ltrim($newAnnotation, '@'))[0];
                $comment             = $node->getDocComment();

                if ($comment && FALSE !== strpos($comment->getText(), $oldAnnotation))
                {
                    $this->requiredNamespaceImports[$annotationNamespace] = TRUE;

                    $text = $comment->getText();
                    $text = str_replace($oldAnnotation, $newAnnotation, $text);

                    $comment->setText($text);
                }
            }
        }
    }



}