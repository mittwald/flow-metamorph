<?php
namespace Mw\Metamorph\Transformation\Task;

use Mw\Metamorph\Domain\Model\MorphConfiguration;

class TaskQueue {

	private $knownHashes = [];

	public function __construct() {
		$this->queue = new \SplPriorityQueue();
	}

	public function executeAll(MorphConfiguration $configuration, callable $log = NULL) {
		while (!$this->isEmpty()) {
			$task = $this->dequeue();

			if ($log) {
				call_user_func(
					$log,
					vsprintf(
						'Executing deferred task <comment>%s</comment>: <info>%s</info>',
						[substr($task->getHash(), 0, 7), $task->toString()]
					)
				);
			}

			$task->execute($configuration, $this);
		}
	}

	public function enqueue(TaskInterface $task, $priority = 0) {
		$hash = $task->getHash();
		if (FALSE === array_key_exists($hash, $this->knownHashes)) {
			$this->queue->insert($task, $priority);
			$this->knownHashes[$hash] = TRUE;
		}
	}

	/**
	 * @return TaskInterface
	 */
	public function dequeue() {
		return $this->queue->extract();
	}

	public function isEmpty() {
		return $this->queue->isEmpty();
	}

}