<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Strategy;


use Mw\Metamorph\Domain\Model\MorphConfiguration;


interface MigrationStrategyInterface
{



    public function execute(MorphConfiguration $configuration);



}
