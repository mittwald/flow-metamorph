<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

class ExtbaseLegacyConversionStrategy extends AbstractExtbaseConversionStrategy {

	/**
	 * @param string $className    A TYPO3 CMS class name.
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name.
	 */
	public function accept($className, $extensionKey) {
		if (0 !== strpos($className, 'Tx_')) {
			return FALSE;
		}

		return parent::accept($className, $extensionKey);
	}

	protected function getNamespaceSeparator() {
		return '_';
	}
}