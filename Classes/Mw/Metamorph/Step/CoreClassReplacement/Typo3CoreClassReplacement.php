<?php
namespace Mw\Metamorph\Step\CoreClassReplacement;

class Typo3CoreClassReplacement implements ClassReplacement {

	static protected $typo3ClassPattern           = ',^\\\\?(?P<outer>TYPO3\\\\CMS\\\\(?P<package>Extbase|Fluid|Core|Frontend)\\\\(?P<inner>[A-Za-z0-9\\\\]+))$,';

	static protected $typo3AnnotationClassPattern = ',@(?:var|param|return)\s+\\\\?(?P<outer>TYPO3\\\\CMS\\\\(?P<package>Extbase|Fluid|Core|Frontend)\\\\(?P<inner>[A-Za-z0-9\\\\]+)),';

	public function replaceInComment($comment) {
		$comment = preg_replace_callback(static::$typo3AnnotationClassPattern, $this->getReplacer(TRUE), $comment);
		return $comment;
	}

	public function replaceName($name) {
		if (preg_match(static::$typo3ClassPattern, $name)) {
			$result = preg_replace_callback(static::$typo3ClassPattern, $this->getReplacer(), $name);
			return strlen($result) > 0 ? $result : NULL;
		}
		return NULL;
	}

	private function getReplacer($inAnnotation = FALSE) {
		return function ($matches) use ($inAnnotation) {
			$possiblePackageKeys = $this->getPossibleNewPackageKeys($matches['package']);
			foreach ($possiblePackageKeys as $packageKey) {
				$flowClassName = $packageKey . '\\' . $matches['inner'];
				if (class_exists($flowClassName) || interface_exists($flowClassName) || trait_exists($flowClassName)) {
					return str_replace($matches['outer'], $flowClassName, $matches[0]);
				}
			}

			if ($inAnnotation) {
				return "@todo No TYPO3 Flow equivalent for this class could be found!\n * " . $matches[0];
			} else {
				return NULL;
			}
		};
	}

	private function getPossibleNewPackageKeys($package) {
		return [
			'Extbase'  => ['Mw\\T3Compat', 'TYPO3\\Flow'],
			'Frontend' => ['Mw\\T3Compat', 'TYPO3\\Flow'],
			'Fluid'    => ['Mw\\T3Compat', 'TYPO3\\Fluid'],
			'Core'     => ['Mw\\T3Compat', 'TYPO3\\Flow'],
		][$package];
	}
}