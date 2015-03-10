<?php
namespace Mw\Metamorph\Tests\Step\CoreClassReplacement;

use Mw\Metamorph\Step\CoreClassReplacement\Typo3CoreClassReplacement;
use Mw\Metamorph\Step\CoreClassReplacement\Typo3LegacyCoreClassReplacement;

require_once('Typo3CoreClassReplacementTest.php');

class Typo3LegacyCoreClassReplacementTest extends Typo3CoreClassReplacementTest {

	/**
	 * @var Typo3CoreClassReplacement
	 */
	protected $replacement;

	public function setUp() {
		$this->replacement = new Typo3LegacyCoreClassReplacement(__DIR__ . '/../../../../Resources/Private/Php/Typo3LegacyClassMap.php');
	}

	public function classNamesWithReplacements() {
		return [
			[
				'Tx_Extbase_MVC_RequestInterface',
				'TYPO3\\Flow\\Mvc\\RequestInterface'
			],
			[
				'Tx_Extbase_MVC_Controller_ActionController',
				'TYPO3\\Flow\\Mvc\\Controller\\ActionController'
			],
			[
				'Tx_Fluid_View_TemplateView',
				'TYPO3\\Fluid\\View\\TemplateView'
			],
			[
				't3lib_div',
				'Mw\\T3Compat\\Utility\\GeneralUtility'
			]
		];
	}

	public function classNamesWithoutReplacements() {
		return [
			['Tx_Extbase_Mvc_SpecialRequestInterface'],
			['t3lib_foobar']
		];
	}

}