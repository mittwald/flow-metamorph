<?php
namespace Mw\Metamorph\Tests\Domain\Model\State;


use Mw\Metamorph\Domain\Model\State\PackageMapping;
use TYPO3\Flow\Tests\UnitTestCase;


class PackageMappingTest extends UnitTestCase
{



    /** @var PackageMapping */
    private $mapping;



    public function setUp()
    {
        $this->mapping = new PackageMapping('/foo/bar/my_extension');
    }



    public function testConstructorDerivesExtensionKeyFromPath()
    {
        $this->assertEquals('my_extension', $this->mapping->getExtensionKey());
    }



    public function testConstructorCanOverrideExtensionKey()
    {
        $mapping = new PackageMapping('/foo/bar/my_extension', 'my_real_extension');
        $this->assertEquals('my_real_extension', $mapping->getExtensionKey());
    }



    public function testPackageKeyIsInvalidWithoutVendorNamespace()
    {
        $this->mapping->setPackageKey('Inventory');
        $this->assertFalse($this->mapping->isPackageKeyValid());
    }



    public function testPackageKeyIsValidWithVendorNamespace()
    {
        $this->mapping->setPackageKey('He.Inventory');
        $this->assertTrue($this->mapping->isPackageKeyValid());
    }

}