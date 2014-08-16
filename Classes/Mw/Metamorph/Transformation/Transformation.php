<?php
namespace Mw\Metamorph\Transformation;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphState;
use Mw\Metamorph\Io\OutputInterface;


interface Transformation
{



    public function execute(MorphConfiguration $configuration, MorphState $state, OutputInterface $out);

}