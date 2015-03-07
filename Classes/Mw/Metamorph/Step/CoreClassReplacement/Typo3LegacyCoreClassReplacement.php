<?php
namespace Mw\Metamorph\Step\CoreClassReplacement;

class Typo3LegacyCoreClassReplacement extends Typo3CoreClassReplacement {

	protected $replacements;

	public function __construct($classMap = NULL){
		$classMap = $classMap ?: 'resource://Mw.Metamorph/Private/Php/Typo3LegacyClassMap.php';
		$this->replacements = include($classMap);
	}

	public function replaceInComment($comment) {
		foreach ($this->replacements as $key => $value) {
			$comment = preg_replace_callback(',' . preg_quote($key) . ',', function($matches) {
				if ($replacement = $this->replaceName($matches[0])) {
					return $replacement;
				} else {
					return $matches[0];
				}
			}, $comment);
		}

		$comment = preg_replace(',@(var|param|return)\s+(t3lib|tslib|Tx_Extbase|Tx_Fluid)_([a-zA-Z0-9_]+),', "@todo No TYPO3 Flow equivalent for this class could be found!\n$0", $comment);

		return $comment;
	}

	public function replaceName($name) {
		if (array_key_exists($name, $this->replacements)) {
			return parent::replaceName($this->replacements[$name]);
		}
		return NULL;
	}
}