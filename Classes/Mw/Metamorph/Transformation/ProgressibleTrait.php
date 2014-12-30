<?php
namespace Mw\Metamorph\Transformation;

trait ProgressibleTrait {

	/**
	 * @var ProgressListener[]
	 */
	private $progressListeners = [];

	public function addProgressListener(ProgressListener $listener) {
		$this->progressListeners[spl_object_hash($listener)] = $listener;
	}

	private function forEachListener(callable $fn) {
		foreach ($this->progressListeners as $listener) {
			call_user_func($fn, $listener);
		}
	}

	protected function startProgress($message, $max) {
		$this->forEachListener(function (ProgressListener $listener) use ($message, $max) {
			$listener->onProgressStart($message, $max);
		});
	}

	protected function advanceProgress() {
		$this->forEachListener(function (ProgressListener $listener) {
			$listener->onProgressAdvance();
		});
	}

	protected function finishProgress() {
		$this->forEachListener(function (ProgressListener $listener) {
			$listener->onProgressFinish();
		});
	}

}