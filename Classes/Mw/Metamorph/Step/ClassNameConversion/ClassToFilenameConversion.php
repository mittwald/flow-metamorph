<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Helmich\Scalars\Types\String;
use TYPO3\Flow\Package\PackageInterface;

/**
 * Helper class for converting relative class file names into absolute file names
 *
 * @package    Mw\Metamorph
 * @subpackage Step\ClassNameConversion
 */
class ClassToFilenameConversion {

	/**
	 * Determines if a class contains a test case.
	 *
	 * @param \Helmich\Scalars\Types\String $relativeFilename The relative class file name.
	 * @return bool TRUE if the class contains a test case, otherwise FALSE.
	 */
	private function isClassTestCase(String $relativeFilename) {
		return $relativeFilename->strip('/')->contains('Tests/') || $relativeFilename->endsWidth('Test.php');
	}

	/**
	 * Gets the absolute target filename for a class file.
	 *
	 * @param \Helmich\Scalars\Types\String $relativeFilename The relative class file name (auto-derived from class name).
	 * @param PackageInterface              $package          The target package.
	 * @return string The target filename.
	 */
	public function getAbsoluteFilename(String $relativeFilename, PackageInterface $package) {
		if (FALSE === $this->isClassTestCase($relativeFilename)) {
			return (new String($package->getClassesPath()))
				->stripRight('/')
				->append('/')
				->append($relativeFilename)
				->toPrimitive();
		} else {
			return (new String(''))
				->append($package->getPackagePath())
				->stripRight('/')
				->append('/Tests/Unit/')
				->append(
					$relativeFilename
						->replace('Tests/', '')
						->replace((new String($package->getPackageKey()))->replace('.', '/')->append('/'), '')
				)
				->toPrimitive();
		}
	}
} 