<?php
namespace Mw\Metamorph\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Domain\Exception\MorphNotFoundException;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;
use Mw\Metamorph\Domain\Service\MorphServiceInterface;
use Mw\Metamorph\Logging\LoggingWrapper;
use Mw\Metamorph\View\DecoratedOutput;
use Mw\Metamorph\View\Prompt\MorphCreationDataPrompt;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Object\DependencyInjection\DependencyProxy;

/**
 * @Flow\Scope("singleton")
 */
class MorphCommandController extends CommandController {

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
	 * @Flow\Inject(lazy=false)
	 */
	protected $console;

	private function initializeLogging() {
		// Workaround; apparently, lazy dependency injection cannot be switched off here (perhaps a bug in Flow?)
		if ($this->console instanceof DependencyProxy) {
			$this->console->_activateDependency();
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
	public function createCommand($packageKey, $nonInteractive = FALSE) {
		$this->initializeLogging();

		$input  = new ArrayInput([]);
		$output = new DecoratedOutput($this->console);

		$data = new MorphCreationDto();

		if (FALSE === $nonInteractive) {
			$helperSet = new HelperSet(array(new FormatterHelper()));

			$helper = new QuestionHelper();
			$helper->setHelperSet($helperSet);

			$prompt = new MorphCreationDataPrompt($input, $output, $helper);
			$prompt->setValuesOnCreateDto($data);
		}

		$this->morphService->create($packageKey, $data);
	}

	/**
	 * List available morphs.
	 *
	 * @param bool $quiet Set this flag to generate less verbose (and machine-readable) output.
	 * @return void
	 */
	public function listCommand($quiet = FALSE) {
		$this->initializeLogging();
		$morphs = $this->morphConfigurationRepository->findAll();

		if (count($morphs)) {
			$table = new Table($this->console);
			$table->setHeaders(['Name', 'Source directory']);

			foreach ($morphs as $morph) {
				$table->addRow([$morph->getName(), $morph->getSourceDirectory()]);
			}

			if ($quiet) {
				$table->setStyle('compact');
			}

			$table->render();
		} elseif (!$quiet) {
			$this->outputLine('Found <comment>no</comment> morph configurations.');
			$this->outputLine('Use <comment>./flow morph:create</comment> to create a morph configuration.');
		}
	}

	/**
	 * Morph a TYPO3 CMS application.
	 *
	 * @param string $morphConfigurationName The name of the morph configuration to execute.
	 * @param bool   $reset                  Completely reset stored state before beginning.
	 * @throws \Mw\Metamorph\Domain\Exception\MorphNotFoundException
	 * @return void
	 */
	public function executeCommand($morphConfigurationName, $reset = FALSE) {
		$this->initializeLogging();
		$morph = $this->morphConfigurationRepository->findByIdentifier($morphConfigurationName);

		if ($morph === NULL) {
			throw new MorphNotFoundException(
				'No morph configuration with identifier <b>' . $morphConfigurationName . '</b> found!',
				1399993315
			);
		}

		if (TRUE === $reset) {
			$this->morphService->reset($morph);
		}

		try {
			$this->morphService->execute($morph);
		} catch (HumanInterventionRequiredException $e) {
		} catch (\Exception $e) {
			$this->output->outputLine('<error>  UNCAUGHT EXCEPTION  </error>');
			$this->output->outputLine('  ' . get_class($e) . ': ' . $e->getMessage());
			$this->output->output($e->getTraceAsString());
			$this->sendAndExit(1);
		}
	}

}
