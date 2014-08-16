<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;


class ExtensionInventory extends AbstractTransformation
{


    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $rootDirectory     = $configuration->getSourceDirectory() . '/typo3conf/ext';
        $directoryIterator = new \DirectoryIterator($rootDirectory);
        $matcher           = $configuration->getExtensionMatcher();

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
                $extensions[] = $extensionKey;
            }
            else
            {
                $out->outputLine('  - EXT:<i>%s</i>: <u>IGNORING</u>', [$extensionKey]);
            }
        }

        if (isset($packageMapping['extensions']))
        {
            foreach ($packageMapping['extensions'] as $key => $value)
            {
                if (!in_array($key, $extensions))
                {
                    unset($packageMapping['extensions'][$key]);
                }
            }
        }

        foreach ($extensions as $extension)
        {
            if (!isset($packageMapping['extensions']) || !array_key_exists($extension, $packageMapping['extensions']))
            {
                $packageMapping['reviewed']               = FALSE;
                $packageMapping['extensions'][$extension] = [
                    'action'     => 'MORPH',
                    'packageKey' => $this->convertExtensionKeyToPackageName($extension)
                ];
            }
        }

        $state->writeYamlFile('PackageMap', $packageMapping);
    }



    private function convertExtensionKeyToPackageName($extensionKey)
    {
        return str_replace(' ', '.', ucwords(str_replace('_', ' ', $extensionKey)));
    }
}