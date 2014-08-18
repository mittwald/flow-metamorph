<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Io\OutputInterface;
use TYPO3\Eel\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;
use TYPO3\Flow\Utility\Files;



class CreatePackages extends AbstractTransformation
{



    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out)
    {
        $packageMap = $state->readYamlFile('PackageMap', TRUE);

        /** @var PackageMapping[] $packages */
        $packages = [];

        foreach ($packageMap['extensions'] as $extensionConfiguration)
        {
            $packages[] = PackageMapping::jsonUnserialize($extensionConfiguration);
        }

        foreach ($packages as $package)
        {
            $this->packageManager->createPackage(
                $package->getPackageKey(),
                $this->createPackageMetaData($package),
                NULL,
                'typo3-flow-package'
            );
            $out->outputLine('  - Created package <i>%s</i>', [$package->getPackageKey()]);
        }
    }



    private function createPackageMetaData(PackageMapping $packageMapping)
    {
        $metaData = new MetaData($packageMapping->getPackageKey());
        $metaData->setDescription($packageMapping->getDescription());
        $metaData->setVersion($packageMapping->getVersion());
        $metaData->setPackageType('typo3-flow-package');

        foreach ($packageMapping->getAuthors() as $author)
        {
            $metaData->addParty(new MetaData\Person('Developer', $author['name'], isset($author['email']) ? $author['email'] : NULL));
        }

        return $metaData;
    }
} 