<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Transformation\DatabaseMigration\Visitor\FullMigrationVisitor;
use PhpParser\Lexer;
use TYPO3\Flow\Annotations as Flow;

class FullMigrationStrategy extends AbstractMigrationStategy {

	/**
	 * @return FullMigrationVisitor
	 */
	protected function getMigrationVisitor() {
		$migrationVisitor = new FullMigrationVisitor($this->tca, $this->taskQueue);
		return $migrationVisitor;
	}

}