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
//			new PatternBasedReplacement()
		];
	}

	public function replaceInComment($comment) {
		foreach($this->classReplacements as $replacement) {
			$comment = $replacement->replaceInComment($comment);
		}
		return $comment;
	}

	public function replaceName($name) {
		foreach($this->classReplacements as $replacement) {
			$name = $replacement->replaceName($name);
			if ($name !== NULL) {
				return $name;
			}
		}
		return NULL;
	}
}