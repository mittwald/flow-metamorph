<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;


class ExtensionInventory extends AbstractTransformation
{



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $rootDirectory     = $configuration->getSourceDirectory() . '/typo3conf/ext';
        $directoryIterator = new \DirectoryIterator($rootDirectory);
        $matcher           = $configuration->getExtensionMatcher();

        /** @var PackageMapping[] $extensions */
        $extensions = [];

        $packageMapping = $state->readYamlFile('PackageMap', FALSE);

        foreach ($directoryIterator as $directoryInfo)
        {
            /** @var \DirectoryIterator $directoryInfo */
            if (!$directoryInfo->isDir() || !file_exists($directoryInfo->getPathname() . '/ext_emconf.php'))
            {
                continue;
            }

            $extensionKey = $directoryInfo->getBasename();

            if ($matcher->match($extensionKey))
            {
                $out->outputLine('  - EXT:<i>%s</i>: <u>FOUND</u>', [$extensionKey]);

                $mapping = new PackageMapping($directoryInfo->getPathname(), $extensionKey);
                $mapping->setPackageKey($this->convertExtensionKeyToPackageName($extensionKey));

                $extensions[$extensionKey] = $mapping;
            }
            else
            {
                $out->outputLine('  - EXT:<i>%s</i>: <u>IGNORING</u>', [$extensionKey]);
            }
        }

        // Remove extensions that are defined in the package map, but not present anymore
        // in the source directory.
        if (isset($packageMapping['extensions']))
        {
            foreach ($packageMapping['extensions'] as $key => $value)
            {
                if (!array_key_exists($key, $extensions))
                {
                    unset($packageMapping['extensions'][$key]);
                }
            }
        }

        foreach ($extensions as $extension)
        {
            if (!isset($packageMapping['extensions']) || !array_key_exists($extension->getExtensionKey(), $packageMapping['extensions']))
            {
                $packageMapping['reviewed']                                  = FALSE;
                $packageMapping['extensions'][$extension->getExtensionKey()] = $extension->jsonSerialize();
            }
        }

        $state->writeYamlFile('PackageMap', $packageMapping);
    }



    private function convertExtensionKeyToPackageName($extensionKey)
    {
        return str_replace(' ', '.', ucwords(str_replace('_', ' ', $extensionKey)));
    }
}