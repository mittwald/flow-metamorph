<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Step\CoreClassReplacement\ClassReplacement;
use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Node;
use TYPO3\Flow\Annotations as Flow;

class ExtbaseClassReplacementVisitor extends AbstractVisitor {

	/**
	 * @var ClassReplacement
	 * @Flow\Inject
	 */
	protected $classReplacement;

	public function enterNode(Node $node) {
		if ($node->getDocComment()) {
			$text = $node->getDocComment()->getText();
			$text = $this->classReplacement->replaceInComment($text);

			$node->getDocComment()->setText($text);
		}

		// The class Doctrine\Common\Collection\Collection is just an interface.
		// When used in a "new" operator, a concrete implementation has to be used
		// (usually, the ArrayCollection is the best choice).
		if ($node instanceof Node\Expr\New_) {
			if ($node->class instanceof Node\Name && ($node->class == 'TYPO3\CMS\Extbase\Persistence\ObjectStorage' || $node->class == 'Tx_Extbase_Persistence_ObjectStorage')) {
				$node->class = new Node\Name\FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection');
			}
		}

		if ($node instanceof Node\Name) {
			$name = $node->toString();
			if ($replacement = $this->classReplacement->replaceName($name)) {
				return new Node\Name\FullyQualified($replacement);
			}
		}

		return NULL;
	}

}