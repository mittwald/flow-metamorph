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
use Mw\Metamorph\Domain\Service\Aspect\MorphCreationAspect;
use Mw\Metamorph\Domain\Service\Aspect\MorphExecutionAspect;
use Mw\Metamorph\Domain\Service\Aspect\MorphResetAspect;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;


/**
 * Class MorphService
 *
 * @package Mw\Metamorph\Domain\Service
 * @Flow\Scope("singleton")
 */
class MorphService implements MorphServiceInterface
{



    /**
     * @var MorphCreationAspect
     * @Flow\Inject
     */
    protected $creationAspect;


    /**
     * @var MorphResetAspect
     * @Flow\Inject
     */
    protected $resetAspect;


    /**
     * @var MorphExecutionAspect
     * @Flow\Inject
     */
    protected $executionAspect;



    public function reset(MorphConfiguration $configuration, OutputInterface $out)
    {
        $this->resetAspect->reset($configuration, $out);
    }



    public function create($packageKey, MorphCreationData $data, OutputInterface $out)
    {
        $this->creationAspect->create($packageKey, $data, $out);
    }



    public function execute(MorphConfiguration $configuration, OutputInterface $out)
    {
        $this->executionAspect->execute($configuration, $out);
    }

}
