<?php
namespace Mw\Metamorph\Transformation\Helper;

use PhpParser\Comment;
use PhpParser\Node;

class CommentHelper {

	public function addCommentToNode(Node $node, $comment) {
		$commentNode = new Comment('// ' . $comment);

		$comments   = $node->getAttribute('comments', []);
		$comments[] = $commentNode;

		$node->setAttribute('comments', $comments);
		return $node;
	}
}