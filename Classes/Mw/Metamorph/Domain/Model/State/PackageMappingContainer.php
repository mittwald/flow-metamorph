<?php
namespace Mw\Metamorph\Domain\Model\State;


class PackageMappingContainer
{



    use Reviewable;



    /** @var PackageMapping[] */
    protected $packageMappings = [];



    /**
     * @return PackageMapping[]
     */
    public function getPackageMappings()
    {
        return $this->packageMappings;
    }



    public function addPackageMapping(PackageMapping $packageMapping)
    {
        if (FALSE === $this->hasPackageMapping($packageMapping->getExtensionKey()))
        {
            $this->reviewed          = FALSE;
            $this->packageMappings[] = $packageMapping;
        }
    }



    public function hasPackageMapping($extensionKey)
    {
        return NULL !== $this->getPackageMapping($extensionKey);
    }



    public function getPackageMapping($extensionKey)
    {
        foreach ($this->packageMappings as $packageMapping)
        {
            if ($packageMapping->getExtensionKey() === $extensionKey)
            {
                return $packageMapping;
            }
        }
        return NULL;
    }



    public function removePackageMapping($extensionKey)
    {
        foreach ($this->packageMappings as $key => $packageMapping)
        {
            if ($packageMapping->getExtensionKey() === $extensionKey)
            {
                unset($this->packageMappings[$key]);
            }
        }
        $this->packageMappings = array_values($this->packageMappings);
    }



}