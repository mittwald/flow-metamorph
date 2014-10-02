<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\OptionParser;
use Mw\Metamorph\Transformation\Helper\Namespaces\ImportHelper;
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

            '/@validate[ ]+(?:\$?(?<var>[a-zA-Z0-9_]+)[ ]+)?(?<type>[A-Za-z_\\\\]+)(?:\((?<options>.*)\))?/' => function(array $m)
            {
                $renderer = new AnnotationRenderer('Flow', 'Validate');
                $renderer->addParameter('type', $m['type']);

                if (isset($m['var']) && strlen($m['var']) > 0)
                {
                    $renderer->addParameter('argumentName', $m['var']);
                }

                if (isset($m['options']))
                {
                    $renderer->addParameter('options', (new OptionParser($m['options']))->getValues());
                }

                return $renderer->render();
            },

            '/@dontvalidatehmac/' => '@Flow\\SkipCsrfProtection',
            '/@dontvalidate(?:\s+\$?(?<var>.+))?/' => function(array $m)
            {
                $renderer = new AnnotationRenderer('Flow', 'IgnoreValidation');

                if (isset($m['var']))
                {
                    $renderer->addParameter('argumentName', $m['var']);
                }

                return $renderer->render();
            },

            '/@scope\s+(?<scope>singleton|prototype)/' => function (array $m)
            {
                return (new AnnotationRenderer('Flow', 'Scope'))
                    ->setArgument($m['scope'])
                    ->render();
            },

            '/@dontverifyrequesthash/' => '@Flow\\SkipCsrfProtection',
            '/@lazy/' => '@Flow\\Lazy'
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
            $helper = new ImportHelper();
            foreach($this->requiredNamespaceImports as $alias => $_)
            {
                $namespace = $this->namespaceMappings[$alias];
                $node = $helper->importNamespaceIntoOtherNamespace($node, $namespace, $alias);
            }
            return $node;
        }
        else
        {
            foreach ($this->annotationMapping as $oldAnnotation => $newAnnotation)
            {
                $comment = $node->getDocComment();
                if ($comment && FALSE !== preg_match($oldAnnotation, $comment->getText()))
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
        return NULL;
    }



}