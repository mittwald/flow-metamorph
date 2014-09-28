<?php
namespace Mw\Metamorph\Persistence\Mapping;


use Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\PatternExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\UnionMatcher;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;
use TYPO3\Flow\Utility\Files;


class MorphConfigurationWriter
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function createMorph(MorphConfiguration $morphConfiguration)
    {
        $metaData = new MetaData($morphConfiguration->getName());
        $package  = $this->packageManager->createPackage($morphConfiguration->getName(), $metaData);

        $morphData = [
            'sourceDirectory'       => $morphConfiguration->getSourceDirectory(),
            'extensions'            => $this->exportExtensionMatcher($morphConfiguration->getExtensionMatcher()),
            'tableStructureMode'    => $morphConfiguration->getTableStructureMode(),
            'pibaseRefactoringMode' => $morphConfiguration->getPibaseRefactoringMode()
        ];

        $configurationPath = $package->getConfigurationPath();
        $morphPath         = Files::concatenatePaths([$configurationPath, 'Metamorph', 'Morph.yml']);

        Files::createDirectoryRecursively(dirname($morphPath));
        file_put_contents($morphPath, Yaml::dump($morphData));
    }



    public function removeMorph(MorphConfiguration $morphConfiguration)
    {
        $this->packageManager->deletePackage($morphConfiguration->getName());
    }



    private function exportExtensionMatcher(ExtensionMatcher $matcher)
    {
        if ($matcher instanceof PatternExtensionMatcher)
        {
            return ['pattern' => $matcher->getPattern()];
        }
        else if ($matcher instanceof UnionMatcher)
        {
            return array_map(array($this, 'exportExtensionMatcher'), $matcher->getMatchers());
        }
        return NULL;
    }

} 