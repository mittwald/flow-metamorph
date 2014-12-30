<?php
namespace Mw\Metamorph\Transformation\Helper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use PhpParser\Comment;
use PhpParser\Node;

/**
 * Helper class for adding inline comments to nodes.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Helper
 */
class CommentHelper {

	/**
	 * Adds an inline comment (starting with "//") to a node.
	 *
	 * @param Node   $node    The node to add the comment to
	 * @param string $comment The comment to add
	 * @return Node The modified node
	 */
	public function addCommentToNode(Node $node, $comment) {
		$commentNode = new Comment('// ' . $comment);

		$comments   = $node->getAttribute('comments', []);
		$comments[] = $commentNode;

		$node->setAttribute('comments', $comments);
		return $node;
	}
}