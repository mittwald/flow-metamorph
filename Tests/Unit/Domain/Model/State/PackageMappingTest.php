<?php
namespace Mw\Metamorph\Tests\Domain\Model\State;

use Mw\Metamorph\Domain\Model\State\PackageMapping;
use TYPO3\Flow\Tests\UnitTestCase;

class PackageMappingTest extends UnitTestCase {

	/** @var PackageMapping */
	private $mapping;

	public function setUp() {
		$this->mapping = new PackageMapping('/foo/bar/my_extension');
	}

	public function testConstructorDerivesExtensionKeyFromPath() {
		$this->assertEquals('my_extension', $this->mapping->getExtensionKey());
	}

	public function testConstructorCanOverrideExtensionKey() {
		$mapping = new PackageMapping('/foo/bar/my_extension', 'my_real_extension');
		$this->assertEquals('my_real_extension', $mapping->getExtensionKey());
	}

	/** @test */
	public function filesAreExcludedWhenExludePatternsMatch() {
		$this->mapping->addFileExcludePattern(',.*/vendor/.*,');
		$this->assertFalse($this->mapping->isFileIncluded('path/vendor/foo.php'));
		$this->assertTrue($this->mapping->isFileIncluded('path/lib/foo.php'));
	}

}