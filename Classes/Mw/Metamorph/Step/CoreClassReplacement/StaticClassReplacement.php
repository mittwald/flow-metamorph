<?php
namespace Mw\Metamorph\Step\CoreClassReplacement;

use TYPO3\Flow\Annotations as Flow;

class StaticClassReplacement implements ClassReplacement {

	/**
	 * @var array
	 * @Flow\Inject(setting="staticReplacements")
	 */
	protected $replacements;

	public function replaceInComment($comment) {
		foreach ($this->replacements as $old => $new) {
			if (strpos($comment, $old) !== FALSE) {
				$comment = preg_replace(
					',\\\\?' . preg_quote($old) . ',',
					'\\' . $new,
					$comment
				);
			}
		}
		return $comment;
	}

	public function replaceName($name) {
		if (array_key_exists($name, $this->replacements)) {
			return $this->replacements[$name];
		}
		return NULL;
	}
}