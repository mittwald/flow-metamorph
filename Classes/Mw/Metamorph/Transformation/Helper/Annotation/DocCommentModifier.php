<?php
namespace Mw\Metamorph\Transformation\Helper\Annotation;


use PhpParser\Comment\Doc;


class DocCommentModifier
{



    public function addAnnotationToDocComment(Doc $comment, AnnotationRenderer $annotation)
    {
        $text = $comment->getReformattedText();
        $text = $this->addAnnotationToDocCommentString($text, $annotation);

        $comment->setText($text);
    }



    public function addAnnotationToDocCommentString($commentString, AnnotationRenderer $annotation)
    {
        $lines = explode("\n", $commentString);
        $count = count($lines);

        $lines[$count - 1] = ' * ' . $annotation->render();
        $lines[$count]     = ' */';

        return implode("\n", $lines);
    }



}