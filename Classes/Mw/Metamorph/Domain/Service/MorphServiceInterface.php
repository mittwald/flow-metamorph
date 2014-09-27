<?php
namespace Mw\Metamorph\Domain\Service;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\MorphCreationData;
use Symfony\Component\Console\Output\OutputInterface;


interface MorphServiceInterface
{



    public function reset(MorphConfiguration $configuration, OutputInterface $out);



    public function create($packageKey, MorphCreationData $data, OutputInterface $out);



    public function execute(MorphConfiguration $configuration, OutputInterface $out);

}