<?php
namespace Mw\Metamorph\Tests\Step\ClassNameConversion;

use TYPO3\Flow\Tests\UnitTestCase;

class PibasePluginConversionStrategyTest extends UnitTestCase {

	/** @var \Mw\Metamorph\Step\ClassNameConversion\PibasePluginConversionStrategy */
	private $strategy;

	public function setUp() {
		$this->strategy = new \Mw\Metamorph\Step\ClassNameConversion\PibasePluginConversionStrategy();
	}

	public function testAcceptAcceptsPiClassnames() {
		$this->assertTrue($this->strategy->accept('tx_heexample_pi1', 'he_example'));
	}

	public function testAcceptRejectsPiClassnamesFromForeignExtensions() {
		$this->assertFalse($this->strategy->accept('tx_hewrongexample_pi1', 'he_example'));
	}

	public function testAcceptRejectsExtbaseClassnames() {
		$this->assertFalse($this->strategy->accept('Helmich\\HeExample\\Controller\\FooController', 'he_example'));
	}

	public function testAcceptRejectsExtbaseLegacyClassnames() {
		$this->assertFalse($this->strategy->accept('Tx_HeExample_Controller_FooController', 'he_example'));
	}

	/**
	 * @dataProvider getPiClassnames
	 */
	public function testPiClassnamesAreConvertedCorrectly($className, $fileName, $expected) {
		$this->assertEquals(
			$expected,
			$this->strategy->convertClassName('Mw\\Example', $className, $fileName, 'he_example')
		);
	}

	public function getPiClassnames() {
		return [
			['tx_heexample_pi1', 'pi1/class.tx_heexample_pi1.php', 'Mw\\Example\\Plugin\\Pi1Plugin'],
			['tx_heexample_pi5', 'pi5/class.tx_heexample_pi5.php', 'Mw\\Example\\Plugin\\Pi5Plugin'],
			['tx_heexample_pi', 'pi1/class.tx_heexample_pi.php', 'Mw\\Example\\Plugin\\PiPlugin'],
		];
	}
}