<?php
namespace Mw\Metamorph\Step\DatabaseMigration\Strategy;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Transformation\Task\TaskQueue;

interface MigrationStrategyInterface {

	public function execute(MorphConfiguration $configuration);

	public function setDeferredTaskQueue(TaskQueue $queue);

}
