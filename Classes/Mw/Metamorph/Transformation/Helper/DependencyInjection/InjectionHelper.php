<?php
namespace Mw\Metamorph\Transformation\Helper\DependencyInjection;


use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;


class InjectionHelper
{



    public function injectDependencyIntoClass(Class_ $classNode, $dependency, $name, $lazy = TRUE)
    {
        $found             = FALSE;
        $lastPropertyIndex = NULL;

        $dependency = '\\' . ltrim($dependency, '\\');

        foreach ($classNode->stmts as $i => $classStmt)
        {
            if (!$classStmt instanceof Property)
            {
                continue;
            }

            $lastPropertyIndex = $i;
            foreach ($classStmt->props as $prop)
            {
                if ($prop->name === $name)
                {
                    $found = TRUE;
                }
            }
        }

        if (TRUE === $found)
        {
            return $classNode;
        }

        $annotation = new AnnotationRenderer('Flow', 'Inject');
        if (FALSE === $lazy)
        {
            $annotation->addParameter('lazy', FALSE);
        }

        $comment = new Doc("/**\n * @var $dependency\n * " . $annotation->render() . "\n */");

        $innerProperty = new PropertyProperty($name);
        $outerProperty = new Property(Class_::MODIFIER_PROTECTED, [$innerProperty]);
        $outerProperty->setAttribute('comments', [$comment]);

        $stmts = $classNode->stmts;

        if (NULL === $lastPropertyIndex)
        {
            $stmts = array_merge([$outerProperty], $classNode->stmts);
        }
        else
        {
            array_splice($stmts, $lastPropertyIndex + 1, 0, [$outerProperty]);
        }

        $classNode->stmts = $stmts;
        return $classNode;
    }

} 