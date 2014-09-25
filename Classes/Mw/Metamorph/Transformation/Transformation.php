<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Io\OutputInterface;


interface Transformation
{



    public function setSettings(array $settings);



    public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out);

}