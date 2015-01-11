<?php
namespace Mw\Metamorph\Step\TransformationVisitor;

use Mw\Metamorph\Transformation\TransformationVisitor\AbstractVisitor;
use PhpParser\Node;

class ExtbaseClassReplacementVisitor extends AbstractVisitor {

	/**
	 * @var array
	 */
	private $replacements;

	public function initializeObject() {
		$this->replacements = $this->settings['staticReplacements'];
	}

	public function enterNode(Node $node) {
		if ($node->getDocComment()) {
			$text = $node->getDocComment()->getText();

			foreach ($this->replacements as $old => $new) {
				if (strpos($text, $old) !== FALSE) {
					$text = str_replace($old, $new, $text);
				}
			}

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
			if (array_key_exists($name, $this->replacements)) {
				return new Node\Name\FullyQualified($this->replacements[$name]);
			}
		}

		return NULL;
	}

}