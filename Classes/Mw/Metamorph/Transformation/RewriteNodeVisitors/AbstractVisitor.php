<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Transformation\Task\TaskQueue;
use PhpParser\NodeVisitorAbstract;

class AbstractVisitor extends NodeVisitorAbstract {

	protected $settings;

	/** @var ClassMappingContainer */
	protected $classMap;

	/** @var TaskQueue */
	protected $taskQueue;

	/** @var MorphConfiguration */
	protected $configuration;

	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	public function setClassMap(ClassMappingContainer $classMap) {
		$this->classMap = $classMap;
	}

	public function setMorphConfiguration(MorphConfiguration $configuration) {
		$this->configuration = $configuration;
	}

	public function setDeferredTaskQueue(TaskQueue $queue) {
		$this->taskQueue = $queue;
	}

}