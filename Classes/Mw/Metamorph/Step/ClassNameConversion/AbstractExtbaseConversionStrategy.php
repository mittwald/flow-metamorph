<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

use Helmich\Scalars\Types\String;

abstract class AbstractExtbaseConversionStrategy implements ClassNameConversionStrategy {

	/**
	 * Returns the namespace separator used for these class names.
	 *
	 * @return string The namespace separator (usually, either "_" or "\").
	 */
	abstract protected function getNamespaceSeparator();

	/**
	 * Converts an extension key to camel-case notation (e.g. "tt_news" to "TtNews").
	 *
	 * @param string $extensionKey The extension key
	 * @return string The extension key in camel-case notation
	 */
	protected function extensionKeyToCamelCase($extensionKey) {
		return (new String($extensionKey))
			->split('_')
			->map(function(String $e) { return $e->toUpperFirst(); })
			->join('')
			->toPrimitive();
	}

	/**
	 * @param string $className    A TYPO3 CMS class name.
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name.
	 */
	public function accept($className, $extensionKey) {
		$separator = $this->getNamespaceSeparator();
		if (FALSE === strpos($className, $separator)) {
			return FALSE;
		}

		$components = explode($separator, $className);
		if (count($components) < 3) {
			return FALSE;
		}

		if ($components[1] !== $this->extensionKeyToCamelCase($extensionKey)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param string $namespaceRoot The PSR-0 namespace root.
	 * @param string $className     A TYPO3 CMS class name.
	 * @param string $filename      The original file name.
	 * @param string $extensionKey  The extension key
	 * @return string An appropriate class name for the migrated TYPO3 Flow package.
	 */
	public function convertClassName($namespaceRoot, $className, $filename, $extensionKey) {
		if (FALSE === $this->accept($className, $extensionKey)) {
			return self::PASS;
		}

		$separator = $this->getNamespaceSeparator();

		$components         = explode($separator, $className);
		$componentsRelative = array_slice($components, 2);

		return $namespaceRoot . '\\' . implode('\\', $componentsRelative);
	}
}