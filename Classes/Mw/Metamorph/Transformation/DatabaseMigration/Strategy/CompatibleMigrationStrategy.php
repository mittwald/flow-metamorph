<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;

use Mw\Metamorph\Transformation\DatabaseMigration\Visitor\CompatibleMigrationVisitor;

/**
 * Database migration strategy that keeps the table structure (if possible).
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Strategy
 */
class CompatibleMigrationStrategy extends AbstractMigrationStategy {

	/**
	 * @return CompatibleMigrationVisitor
	 */
	protected function getMigrationVisitor() {
		$migrationVisitor = new CompatibleMigrationVisitor($this->tca, $this->taskQueue);
		return $migrationVisitor;
	}

}