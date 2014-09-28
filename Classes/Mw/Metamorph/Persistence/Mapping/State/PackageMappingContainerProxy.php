<?php
namespace Mw\Metamorph\Persistence\Mapping\State;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMappingContainer;
use TYPO3\Flow\Annotations as Flow;


class PackageMappingContainerProxy extends PackageMappingContainer
{



    use YamlStorable;



    public function __construct(MorphConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }



    public function initializeObject()
    {
        $this->initializeWorkingDirectory($this->configuration->getName());

        $data            = $this->readYamlFile('PackageMap', FALSE);
        $packageMappings = [];

        foreach ($this->getArrayProperty($data, 'extensions', []) as $extensionKey => $extensionData)
        {
            $packageMappings[] = new PackageMappingProxy($extensionKey, $extensionData);
        }

        $this->packageMappings = $packageMappings;
        $this->reviewed        = $this->getArrayProperty($data, 'reviewed', FALSE);
    }



}