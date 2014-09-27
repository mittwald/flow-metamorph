<?php
namespace Mw\Metamorph\Command;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\DefaultMorphCreationData;
use Mw\Metamorph\Exception\MorphNotFoundException;
use Mw\Metamorph\Io\DecoratedOutput;
use Mw\Metamorph\Io\Prompt\MorphCreationDataPrompt;
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



    /**
     * Creates a new site package with a morph configuration.
     *
     * @param string $packageKey     The package key to use for the morph package.
     * @param bool   $nonInteractive Set this flag to suppress interactive prompts during package creation.
     * @return void
     */
    public function createCommand($packageKey, $nonInteractive = FALSE)
    {
        $output       = new DecoratedOutput($this->output);
        $dataProvider = $nonInteractive
            ? new DefaultMorphCreationData()
            : new MorphCreationDataPrompt($output);

        $this->morphService->create($packageKey, $dataProvider, $output);
    }



    /**
     * List available morphs.
     *
     * @return void
     */
    public function listCommand()
    {
        $morphs = $this->morphConfigurationRepository->findAll();

        if (count($morphs))
        {
            $this->outputLine('Found <comment>%d</comment> morph configurations:', [count($morphs)]);
            $this->outputLine();

            foreach ($morphs as $morph)
            {
                $this->outputFormatted($morph->getName(), [], 4);
            }

            $this->outputLine();
        }
        else
        {
            $this->outputLine('Found <comment>no</comment> morph configurations.');
            $this->outputLine('Use <comment>./flow morph:create</comment> to create a morph configuration.');
        }

    }



    /**
     * Morph a TYPO3 CMS application.
     *
     * @param string $morphConfigurationName The name of the morph configuration to execute.
     * @param bool   $reset                  Completely reset stored state before beginning.
     * @throws \Mw\Metamorph\Exception\MorphNotFoundException
     * @return void
     */
    public function executeCommand($morphConfigurationName, $reset = FALSE)
    {
        $morph = $this->morphConfigurationRepository->findByIdentifier($morphConfigurationName);

        if ($morph === NULL)
        {
            throw new MorphNotFoundException(
                'No morph configuration with identifier <b>' . $morphConfigurationName . '</b> found!',
                1399993315
            );
        }

        if (TRUE === $reset)
        {
            $this->outputLine('Resetting state for morph <b>%s</b>.', [$morph->getName()]);
            $this->morphService->reset($morph, $this->output);
        }

        $this->outputLine('Executing morph <b>%s</b>.', [$morph->getName()]);

        $this->morphService->execute($morph, new DecoratedOutput($this->output));
    }

}