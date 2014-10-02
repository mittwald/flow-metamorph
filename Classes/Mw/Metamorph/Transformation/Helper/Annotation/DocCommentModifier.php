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
        // Ensure that comment opening (/**) is on single line
        if (!preg_match(',^[\t ]*/\*\*[\t ]*\n,', $commentString))
        {
            $commentString = preg_replace(',/\*\*[\t ]*,', "/**\n * ", $commentString);
        }

        // Ensure that comment ending (*/) is on single line
        if (!preg_match(',\n[\t ]*\*/[\t ]*$,', $commentString))
        {
            $commentString = preg_replace(',\*/[\t ]*$,', "\n */", $commentString);
        }

        $lines = explode("\n", $commentString);
        $count = count($lines);

        // Ensure that annotations are in a separate paragraph in longer comments.
        if ($count > 2 && trim($lines[$count - 2]) !== '*' && !preg_match(',^[\t ]*\*[\t ]*@,', $lines[$count - 2]))
        {
            $lines[$count - 1] = ' *';
            $count++;
        }

        $lines[$count - 1] = ' * ' . $annotation->render();
        $lines[$count]     = ' */';

        // Remove trailing whitespaces.
        foreach ($lines as &$line)
        {
            $line = rtrim($line);
        }

        return implode("\n", $lines);
    }



}