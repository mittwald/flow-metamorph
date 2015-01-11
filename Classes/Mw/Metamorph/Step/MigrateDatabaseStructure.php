<?php
namespace Mw\Metamorph\Step;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Step\DatabaseMigration\Strategy\CompatibleMigrationStrategy;
use Mw\Metamorph\Step\DatabaseMigration\Strategy\FullMigrationStrategy;
use Mw\Metamorph\Step\DatabaseMigration\Strategy\MigrationStrategyInterface;
use Mw\Metamorph\Transformation\AbstractTransformation;
use Mw\Metamorph\Transformation\Progressible;
use Mw\Metamorph\Transformation\ProgressibleTrait;
use Mw\Metamorph\Transformation\Task\TaskQueue;

class MigrateDatabaseStructure extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		/** @var MigrationStrategyInterface $migrator */
		$migrator = NULL;

		switch ($configuration->getTableStructureMode()) {
			case MorphConfiguration::TABLE_STRUCTURE_MIGRATE:
				$migrator = new FullMigrationStrategy();
				break;
			case MorphConfiguration::TABLE_STRUCTURE_KEEP:
				$migrator = new CompatibleMigrationStrategy();
				break;
			default:
				return;
		}

		$queue = new TaskQueue();

		$migrator->setDeferredTaskQueue($queue);
		$migrator->execute($configuration);

		$this->startProgress('Cleaning up', 0);
		$queue->executeAll($configuration, function ($m) { $this->advanceProgress(); });
		$this->finishProgress();
	}
}
