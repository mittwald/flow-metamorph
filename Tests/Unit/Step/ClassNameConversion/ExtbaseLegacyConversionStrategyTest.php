<?php
namespace Mw\Metamorph\Tests\Step\ClassNameConversion;

use TYPO3\Flow\Tests\UnitTestCase;

class ExtbaseLegacyConversionStrategyTest extends UnitTestCase {

	/** @var \Mw\Metamorph\Step\ClassNameConversion\ExtbaseLegacyConversionStrategy */
	private $strategy;

	public function setUp() {
		$this->strategy = new \Mw\Metamorph\Step\ClassNameConversion\ExtbaseLegacyConversionStrategy();
	}

	public function testAcceptAcceptsPseudonamespacedClasses() {
		$this->assertTrue($this->strategy->accept('Tx_HeExample_Controller_FooController', 'he_example'));
	}

	public function testAcceptRejectsPseudonamespacedClassesWithWrongExtensionKey() {
		$this->assertFalse($this->strategy->accept('Tx_HeWrongExample_Controller_FooController', 'he_example'));
	}

	public function testAcceptsRejectsClassesWithoutTxPrefix() {
		$this->assertFalse($this->strategy->accept('Foobar_Controller_FooController', 'he_example'));
	}

	public function testAcceptRejectsNamespacedClasses() {
		$this->assertFalse($this->strategy->accept('Helmich\\HeExample\\Controller\\FooController', 'he_example'));
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
			['Tx_HeExample_Controller_FooController', 'Classes/Controller/FooController.php', 'Mw\\Example\\Controller\\FooController'],
			['Tx_HeExample_Domain_Model_User',        'Classes/Domain/Model/User.php'       , 'Mw\\Example\\Domain\\Model\\User'],
		];
		// @formatter:on
	}

}