<?php
namespace Mw\Metamorph\Step\Task\Builder;

use Mw\Metamorph\Step\Task\AddImportToClassTask;

class AddImportToClassTaskBuilder {

	private $class;

	private $import;

	private $alias = NULL;

	/**
	 * @param string $targetClassName
	 * @return self
	 */
	public function setTargetClassName($targetClassName) {
		$this->class = $targetClassName;
		return $this;
	}

	public function setImport($importNamespace) {
		$this->import = $importNamespace;
		return $this;
	}

	public function setNamespaceAlias($alias) {
		$this->alias = $alias;
		return $this;
	}

	public function buildTask() {
		return new AddImportToClassTask(
			$this->class,
			$this->import,
			$this->alias
		);
	}

} 