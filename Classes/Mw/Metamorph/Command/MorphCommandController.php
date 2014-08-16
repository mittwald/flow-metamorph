<?php
namespace Mw\Metamorph\Command;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 *                                                                        */

use Mw\Metamorph\Exception\MorphNotFoundException;
use Mw\Metamorph\Io\ResponseWrapper;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;


/**
 * @Flow\Scope("singleton")
 */
class MorphCommandController extends CommandController
{


    /**
     * @var \Mw\Metamorph\Domain\Repository\MorphConfigurationRepository
     * @Flow\Inject
     */
    protected $morphConfigurationRepository;


    /**
     * @var \Mw\Metamorph\Domain\Service\MorphService
     * @Flow\Inject
     */
    protected $morphService;



    public function listCommand()
    {
        $commands = $this->morphConfigurationRepository->findAll();

        $this->outputLine('Found <b>%d</b> morph configurations:', [count($commands)]);
        $this->outputLine();

        foreach ($commands as $command)
        {
            $this->outputFormatted($command->getName(), [], 4);
        }

        $this->outputLine();
    }



    /**
     * Morph a TYPO3 CMS application.
     *
     * @param string $morphConfigurationName The name of the morph configuration to execute.
     * @throws \Exception
     * @return void
     */
    public function executeCommand($morphConfigurationName)
    {
        $self  = $this;
        $morph = $this->morphConfigurationRepository->findByIdentifier($morphConfigurationName);

        if ($morph === NULL)
        {
            throw new MorphNotFoundException(
                'No morph configuration with identifier <b>' . $morphConfigurationName . '</b> found!',
                1399993315
            );
        }

        $this->outputLine('Executing morph <b>%s</b>.', [$morph->getName()]);

        $this->morphService->execute($morph, new ResponseWrapper($this->response) );
    }

}