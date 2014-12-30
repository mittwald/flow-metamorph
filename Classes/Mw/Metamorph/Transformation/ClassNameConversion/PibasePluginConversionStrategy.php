<?php
namespace Mw\Metamorph\Transformation\ClassNameConversion;

class PibasePluginConversionStrategy implements ClassNameConversionStrategy {

	/**
	 * @param string $className    A TYPO3 CMS class name
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name
	 */
	public function accept($className, $extensionKey) {
		$extkeyLower = strtolower(str_replace('_', '', $extensionKey));
		$pattern = ",^tx_{$extkeyLower}_pi[0-9]*$,";
		if (preg_match($pattern, $className)) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param string $namespaceRoot The PSR-0 namespace root
	 * @param string $className     A TYPO3 CMS class name
	 * @param string $filename      The original file name
	 * @param string $extensionKey  The extension key
	 * @return string An appropriate class name for the migrated TYPO3 Flow package
	 */
	public function convertClassName($namespaceRoot, $className, $filename, $extensionKey) {
		if (FALSE === $this->accept($className, $extensionKey)) {
			return self::PASS;
		}

		$components = explode('_', $className);
		$lastComponent = array_pop($components);

		return $namespaceRoot . '\\Plugin\\' . ucfirst($lastComponent) . 'Plugin';
	}
}