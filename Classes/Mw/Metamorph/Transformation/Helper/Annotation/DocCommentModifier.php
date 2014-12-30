<?php
namespace Mw\Metamorph\Transformation\Helper\Annotation;

use Helmich\Scalars\Types\String;
use PhpParser\Comment\Doc;

class DocCommentModifier {

	public function addAnnotationToDocComment(Doc $comment, AnnotationRenderer $annotation) {
		$text = $comment->getReformattedText();
		$text = $this->addAnnotationToDocCommentString(new String($text), $annotation);

		$comment->setText($text);
	}

	public function addAnnotationToDocCommentString($commentString, AnnotationRenderer $annotation) {
		$commentString = ($commentString instanceof String) ? $commentString : new String($commentString);

		// Ensure that comment opening (/**) is on single line
		if (!$commentString->regexMatch(',^[\t ]*/\*\*[\t ]*\n,')) {
			$commentString = $commentString->regexReplace(',/\*\*[\t ]*,', "/**\n * ");
		}

		// Ensure that comment ending (*/) is on single line
		if (!$commentString->regexMatch(',\n[\t ]*\*/[\t ]*$,')) {
			$commentString = $commentString->regexReplace(',\*/[\t ]*$,', "\n */");
		}

		$lines = $commentString->split("\n");
		$count = $lines->length();

		// Ensure that annotations are in a separate paragraph in longer comments.
		if ($count > 2
			&& $lines->getString($count - 2)->strip() !== '*'
			&& !$lines->getString($count - 2)->regexMatch(',^[\t ]*\*[\t ]*@,')
		) {
			$lines->set($count - 1, new String(' *'));
			$count++;
		}

		$lines
			->set($count - 1, new String(' * ' . $annotation->render()))
			->set($count, new String(' */'));

		return $lines
			->map(function (String $line) { return $line->stripRight(); })
			->join("\n")
			->toPrimitive();
	}

	public function removeAnnotationFromDocComment(Doc $comment, $annotation) {
		$text = $comment->getReformattedText();
		$text = $this->removeAnnotationFromDocCommentString(new String($text), $annotation);

		$comment->setText($text);
	}

	public function removeAnnotationFromDocCommentString($commentString, $annotation) {
		$commentString = ($commentString instanceof String) ? $commentString : new String($commentString);

		$filterFn = function (String $line) use ($annotation) {
			return !$line->regexMatch(',^\s*\*\s*' . preg_quote($annotation) . ',');
		};

		return $commentString
			->split("\n")
			->filter($filterFn)
			->join("\n")
			->toPrimitive();
	}

}