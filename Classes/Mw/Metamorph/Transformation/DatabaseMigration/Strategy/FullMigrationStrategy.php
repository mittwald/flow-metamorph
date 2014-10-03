<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\Tca;
use Mw\Metamorph\Transformation\DatabaseMigration\Tca\TcaLoader;
use TYPO3\Flow\Annotations as Flow;


class FullMigrationStrategy implements MigrationStrategyInterface
{



    /**
     * @var TcaLoader
     * @Flow\Inject
     */
    protected $tcaLoader;



    public function execute(MorphConfiguration $configuration)
    {
        $this->loadTca($configuration);
    }



    private function loadTca(MorphConfiguration $configuration)
    {
        $packageMappingContainer = $configuration->getPackageMappingContainer();
        $packageMappingContainer->assertReviewed();

        $tca = new Tca();

        foreach($packageMappingContainer->getPackageMappings() as $packageMapping)
        {
            echo "LOAD TCA FOR " . $packageMapping->getExtensionKey() . "\n";
            $this->tcaLoader->loadTcaForPackage($packageMapping, $tca);
        }
    }

} 