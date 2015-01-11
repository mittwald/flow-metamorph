<?php
namespace Mw\Metamorph\Step\DatabaseMigration\Strategy;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Step\DatabaseMigration\Visitor\FullMigrationVisitor;
use PhpParser\Lexer;
use TYPO3\Flow\Annotations as Flow;

class FullMigrationStrategy extends AbstractMigrationStategy {

	/**
	 * @return \Mw\Metamorph\Step\DatabaseMigration\Visitor\FullMigrationVisitor
	 */
	protected function getMigrationVisitor() {
		$migrationVisitor = new FullMigrationVisitor($this->tca, $this->taskQueue);
		return $migrationVisitor;
	}

}