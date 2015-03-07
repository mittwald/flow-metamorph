<?php
namespace Mw\Metamorph\Tests\Step\CoreClassReplacement;

use Mw\Metamorph\Step\CoreClassReplacement\Typo3CoreClassReplacement;
use TYPO3\Flow\Tests\UnitTestCase;

class Typo3CoreClassReplacementTest extends UnitTestCase {

	/**
	 * @var Typo3CoreClassReplacement
	 */
	protected $replacement;

	public function setUp() {
		$this->replacement = new Typo3CoreClassReplacement();
	}

	/**
	 * @test
	 * @dataProvider classNamesWithReplacements
	 */
	public function matchingClassWithKnownReplacementIsReplacedInDocComment($in, $out) {
		if (class_exists($out) === FALSE && interface_exists($out) === FALSE && trait_exists($out) === FALSE) {
			$this->markTestSkipped('Class ' . $out . ' must exist for this test.');
		}
		$comment         = "/**\n * Some comment\n *\n * @var \\$in\n */";
		$expectedComment = "/**\n * Some comment\n *\n * @var \\$out\n */";
		$modifiedComment = $this->replacement->replaceInComment($comment);

		$this->assertEquals($expectedComment, $modifiedComment);
	}

	/**
	 * @test
	 * @dataProvider classNamesWithoutReplacements
	 */
	public function matchingClassWithoutKnownReplacementIsNotReplacedInDocComment($in) {
		$comment         = "/**\n * Some comment\n *\n * @var \\$in\n */";
		$expectedComment =
			"/**\n * Some comment\n *\n * @todo No TYPO3 Flow equivalent for this class could be found!\n * @var \\$in\n */";
		$modifiedComment = $this->replacement->replaceInComment($comment);

		$this->assertEquals($expectedComment, $modifiedComment);
	}

	/**
	 * @test
	 * @dataProvider unrelatedClassNames
	 */
	public function unrelatedClassNameIsNotReplacedInDocComment($in) {
		$comment         = "/**\n * Some comment\n *\n * @var \\$in\n */";
		$modifiedComment = $this->replacement->replaceInComment($comment);

		$this->assertEquals($comment, $modifiedComment);
	}

	/**
	 * @test
	 * @dataProvider classNamesWithReplacements
	 */
	public function matchingClassWithKnownReplacementIsReplacedDirectly($in, $out) {
		if (class_exists($out) === FALSE && interface_exists($out) === FALSE && trait_exists($out) === FALSE) {
			$this->markTestSkipped('Class ' . $out . ' must exist for this test.');
		}
		$this->assertEquals($out, $this->replacement->replaceName($in));
	}

	/**
	 * @test
	 * @dataProvider classNamesWithoutReplacements
	 */
	public function matchingClassWithoutKnownReplacementIsNotReplacedDirectly($in) {
		$this->assertNull($this->replacement->replaceName($in));
	}

	public function classNamesWithReplacements() {
		return [
			[
				'TYPO3\\CMS\\Extbase\\Mvc\\RequestInterface',
				'TYPO3\\Flow\\Mvc\\RequestInterface'
			],
			[
				'TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController',
				'TYPO3\\Flow\\Mvc\\Controller\\ActionController'
			],
			[
				'TYPO3\\CMS\\Fluid\\View\\TemplateView',
				'TYPO3\\Fluid\\View\\TemplateView'
			],
			[
				'TYPO3\\CMS\\Core\\Utility\\GeneralUtility',
				'Mw\\T3Compat\\Utility\\GeneralUtility'
			],
			[
				'TYPO3\CMS\Frontend\Plugin\AbstractPlugin',
				'Mw\T3Compat\Plugin\AbstractPlugin'
			]
		];
	}

	public function classNamesWithoutReplacements() {
		return [
			['TYPO3\\CMS\\Extbase\\Mvc\\SpecialRequestInterface']
		];
	}

	public function unrelatedClassNames() {
		return [
			['Helmich_Foobar']
		];
	}
}