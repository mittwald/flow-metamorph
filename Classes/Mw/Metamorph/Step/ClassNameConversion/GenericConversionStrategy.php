<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

use Helmich\Scalars\Types\String;

class GenericConversionStrategy implements ClassNameConversionStrategy {

	/**
	 * @param string $className    A TYPO3 CMS class name
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name
	 */
	public function accept($className, $extensionKey) {
		return TRUE;
	}

	/**
	 * @param string $namespaceRoot The PSR-0 namespace root
	 * @param string $className     A TYPO3 CMS class name
	 * @param string $filename      The original file name
	 * @param string $extensionKey  The extension key
	 * @return string An appropriate class name for the migrated TYPO3 Flow package
	 */
	public function convertClassName($namespaceRoot, $className, $filename, $extensionKey) {
		$className              = (new String($className))->replace('\\', '_');
		$lcClassName            = $className->toLower();
		$extensionKeyNormalized = (new String($extensionKey))->replace('_', '')->toLower();

		$components   = $className->split('_');
		$lcComponents = $lcClassName->split('_');

		if ($lcComponents->length() >= 2 && $lcComponents[1] == $extensionKeyNormalized) {
			$components = $components->slice(2);
		}

		return (new String($namespaceRoot . '\\'))
			->append(
				$components
					->map(function (String $c) { return $c->toUpperFirst(); })
					->join('\\')
			)->toPrimitive();
	}
}