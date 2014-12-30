<?php
namespace Mw\Metamorph\Transformation\ClassNameConversion;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * An interface definition for class name conversion strategies.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\ClassNameConversion
 */
interface ClassNameConversionStrategy {

	const PASS = NULL;

	/**
	 * @param string $className    A TYPO3 CMS class name
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name
	 */
	public function accept($className, $extensionKey);

	/**
	 * @param string $namespaceRoot The PSR-0 namespace root
	 * @param string $className     A TYPO3 CMS class name
	 * @param string $filename      The original file name
	 * @param string $extensionKey  The extension key
	 * @return string An appropriate class name for the migrated TYPO3 Flow package
	 */
	public function convertClassName($namespaceRoot, $className, $filename, $extensionKey);

}