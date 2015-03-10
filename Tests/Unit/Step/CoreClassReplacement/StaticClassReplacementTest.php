<?php
namespace Mw\Metamorph\Tests\Step\CoreClassReplacement;

use Mw\Metamorph\Step\CoreClassReplacement\StaticClassReplacement;
use TYPO3\Flow\Tests\UnitTestCase;

class StaticClassReplacementTest extends UnitTestCase {

	/** @var StaticClassReplacement */
	protected $replacement;

	public function setUp() {
		$this->replacement = new StaticClassReplacement();
		$this->inject($this->replacement, 'replacements', ['TYPO3\\CMS\\Foo' => 'TYPO3\\Flow\\Foo']);
	}

	/**
	 * @test
	 */
	public function classNameIsReplacedInDocComment() {
		$text = $this->replacement->replaceInComment("Some comment\n@var TYPO3\\CMS\\Foo");
		$this->assertEquals("Some comment\n@var TYPO3\\Flow\\Foo", $text);
	}

	/**
	 * @test
	 */
	public function unknownClassNameIsNotReplacedInDocComment() {
		$text = $this->replacement->replaceInComment("Some comment\n@var Helmich\\Foo");
		$this->assertEquals("Some comment\n@var Helmich\\Foo", $text);
	}

	/**
	 * @test
	 */
	public function classNameIsReplacedDirectly() {
		$text = $this->replacement->replaceName('TYPO3\\CMS\\Foo');
		$this->assertEquals("TYPO3\\Flow\\Foo", $text);
	}

	/**
	 * @test
	 */
	public function unknownClassNameIsNotReplacedDirectly() {
		$text = $this->replacement->replaceName('Helmich\\Foo');
		$this->assertNull($text);
	}
}