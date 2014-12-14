<?php
namespace Mw\Metamorph\Command;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Command\Prompt\MorphCreationDataPrompt;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;
use Mw\Metamorph\Domain\Service\MorphServiceInterface;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Exception\MorphNotFoundException;
use Mw\Metamorph\Io\DecoratedOutput;
use Mw\Metamorph\Logging\LoggingWrapper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Object\DependencyInjection\DependencyProxy;


/**
 * @Flow\Scope("singleton")
 */
class MorphCommandController extends CommandController
{



    /**
     * @var MorphConfigurationRepository
     * @Flow\Inject
     */
    protected $morphConfigurationRepository;


    /**
     * @var MorphServiceInterface
     * @Flow\Inject
     */
    protected $morphService;


    /**
     * @var LoggingWrapper
     * @Flow\Inject
     */
    protected $loggingWrapper;


    /**
     * @var SymfonyConsoleOutput
     * @Flow\Inject(lazy=FALSE)
     */
    protected $console;



    private function initializeLogging()
    {
        // Workaround; apparently, lazy dependency injection cannot be switched off here (perhaps a bug in Flow?)
        if ($this->console instanceof DependencyProxy)
        {
            $this->console->write('');
        }
        $this->loggingWrapper->setOutput(new DecoratedOutput($this->console));
    }



    /**
     * Creates a new site package with a morph configuration.
     *
     * @param string $packageKey     The package key to use for the morph package.
     * @param bool   $nonInteractive Set this flag to suppress interactive prompts during package creation.
     * @return void
     */
    public function createCommand($packageKey, $nonInteractive = FALSE)
    {
        $this->initializeLogging();

        $input  = new ArrayInput([]);
        $output = new DecoratedOutput($this->console);

        $data = new MorphCreationDto();

        if (FALSE === $nonInteractive)
        {
            $helperSet = new HelperSet(array(new FormatterHelper()));

            $helper = new QuestionHelper();
            $helper->setHelperSet($helperSet);

            $prompt = new MorphCreationDataPrompt($input, $output, $helper);
            $prompt->setValuesOnCreateDto($data);
        }

        $this->morphService->create($packageKey, $data, $output);
    }



    /**
     * List available morphs.
     *
     * @return void
     */
    public function listCommand()
    {
        $this->initializeLogging();
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
        $this->initializeLogging();
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
            $this->morphService->reset($morph, $this->console);
        }

        try
        {
            $this->morphService->execute($morph, new DecoratedOutput($this->console));
        }
        catch (HumanInterventionRequiredException $e)
        {
        }
        catch (\Exception $e)
        {
            $this->output->outputLine('<error>  UNCAUGHT EXCEPTION  </error>');
            $this->output->outputLine('  ' . get_class($e) . ': ' . $e->getMessage());
            $this->sendAndExit(1);
        }
    }

}