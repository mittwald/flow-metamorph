<?php
namespace Mw\Metamorph\Step\DatabaseMigration\Strategy;

use Mw\Metamorph\Step\DatabaseMigration\Visitor\CompatibleMigrationVisitor;

/**
 * Database migration strategy that keeps the table structure (if possible).
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Strategy
 */
class CompatibleMigrationStrategy extends AbstractMigrationStategy {

	/**
	 * @return \Mw\Metamorph\Step\DatabaseMigration\Visitor\CompatibleMigrationVisitor
	 */
	protected function getMigrationVisitor() {
		$migrationVisitor = new CompatibleMigrationVisitor($this->tca, $this->taskQueue);
		return $migrationVisitor;
	}

}