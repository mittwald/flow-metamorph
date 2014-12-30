<?php
namespace Mw\Metamorph\Tests\Transformation\ClassNameConversion;

use Mw\Metamorph\Transformation\ClassNameConversion\ExtbaseNamespacedConversionStrategy;
use TYPO3\Flow\Tests\UnitTestCase;

class ExtbaseNamespacedConversionStrategyTest extends UnitTestCase {

	/** @var ExtbaseNamespacedConversionStrategy */
	private $strategy;

	public function setUp() {
		$this->strategy = new ExtbaseNamespacedConversionStrategy();
	}

	public function testAcceptAcceptsNamespacedClasses() {
		$this->assertTrue($this->strategy->accept('Helmich\\HeExample\\Controller\\FooController', 'he_example'));
	}

	public function testAcceptRejectsPseudonamespacedClasses() {
		$this->assertFalse($this->strategy->accept('Tx_HeExample_Controller_FooController', 'he_example'));
	}

	public function testAcceptRejectsShortNamespacedClasses() {
		$this->assertFalse($this->strategy->accept('Foo\\Bar', 'he_example'));
	}

	/**
	 * @dataProvider validClassNameDataProvider
	 * @param $actualClassName
	 * @param $fileName
	 * @param $expectedClassName
	 */
	public function testNamespacedClassNameIsResolvedCorrectly($actualClassName, $fileName, $expectedClassName) {
		$this->assertEquals(
			$expectedClassName,
			$this->strategy->convertClassName('Mw\\Example', $actualClassName, $fileName, 'he_example')
		);
	}

	public function validClassNameDataProvider() {
		// @formatter:off
		return [
			['Helmich\\HeExample\\Controller\\FooController', 'Classes/Controller/FooController.php', 'Mw\\Example\\Controller\\FooController'],
			['Helmich\\HeExample\\Domain\\Model\\User',       'Classes/Domain/Model/User.php'       , 'Mw\\Example\\Domain\\Model\\User'],
		];
		// @formatter:on
	}

}