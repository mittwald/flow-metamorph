<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\DatabaseMigration\Strategy\FullMigrationStrategy;
use Mw\Metamorph\Transformation\DatabaseMigration\Strategy\MigrationStrategyInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDatabaseStructure extends AbstractTransformation
{



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out)
    {
        /** @var MigrationStrategyInterface $migrator */
        $migrator = NULL;

        var_dump($configuration->getTableStructureMode());
        switch ($configuration->getTableStructureMode())
        {
            case MorphConfiguration::TABLE_STRUCTURE_MIGRATE:
                $migrator = new FullMigrationStrategy();
                break;
            case MorphConfiguration::TABLE_STRUCTURE_KEEP:
                $migrator = FALSE;
                break;
            default:
                return;
        }

        echo "EXECUTE " . get_class($migrator) . "\n";
//        $migrator->execute($configuration);
    }
}