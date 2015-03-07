<?php
namespace Mw\Metamorph\Step\CoreClassReplacement;

class ClassReplacementChain implements ClassReplacement {

	/**
	 * @var ClassReplacement[]
	 */
	protected $classReplacements = [];

	public function __construct() {
		$this->classReplacements = [
			new StaticClassReplacement(),
			new Typo3CoreClassReplacement(),
			new Typo3LegacyCoreClassReplacement()
		];
	}

	public function replaceInComment($comment) {
		foreach($this->classReplacements as $replacement) {
			$comment = $replacement->replaceInComment($comment);
		}
		return $comment;
	}

	public function replaceName($originalName) {
		foreach($this->classReplacements as $replacement) {
			if (($replacementName = $replacement->replaceName($originalName)) !== NULL) {
				return $replacementName;
			}
		}
		return NULL;
	}
}
