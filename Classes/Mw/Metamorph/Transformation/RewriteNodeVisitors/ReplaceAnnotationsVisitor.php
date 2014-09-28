<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\Node;


class ReplaceAnnotationsVisitor extends AbstractVisitor
{



    /** @var Node\Stmt\Namespace_ */
    private $currentNamespace;


    /** @var array */
    private $annotationMapping;


    private $namespaceMappings = [
        'Flow' => 'TYPO3\\Flow\\Annotations',
        'ORM'  => 'Doctrine\\ORM\\Mapping'
    ];


    private $requiredNamespaceImports = [];



    public function __construct()
    {
        // @formatter:off
        $this->annotationMapping = [
            '/@inject/'                        => '@Flow\\Inject',
            '/@validate\s+\$?(.*)/'            => function (array $m) { return '@Flow\\Validate("' . ucfirst($m[1]) . '")'; },
            '/@dontvalidate(\s+\$?(.+))?/'     => function (array $m) { return $m[1] ? '@Flow\\IgnoreValidation("' . trim($m[2]) . '")' : '@Flow\\IgnoreValidation'; },
            '/@scope\s+(singleton|prototype)/' => function (array $m) { return '@Flow\\Scope("' . $m[1] . '")'; },
            '/@dontverifyrequesthash/'         => '@Flow\\SkipCsrfProtection'
        ];
        // @formatter:on
    }



    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->currentNamespace         = $node;
            $this->requiredNamespaceImports = [];
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
                $comment = $node->getDocComment();
                if ($comment && FALSE !== strpos($comment->getText(), $oldAnnotation))
                {
                    $text = $comment->getText();
                    $text = is_callable($newAnnotation)
                        ? preg_replace_callback($oldAnnotation, $newAnnotation, $text)
                        : preg_replace($oldAnnotation, $newAnnotation, $text);

                    $comment->setText($text);

                    foreach ($this->namespaceMappings as $alias => $namespace)
                    {
                        if (FALSE !== strstr($text, '@' . $alias . '\\'))
                        {
                            $this->requiredNamespaceImports[$alias] = TRUE;
                        }
                    }
                }
            }
        }
    }



}