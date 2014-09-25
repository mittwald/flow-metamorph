<?php
namespace Mw\Metamorph\Io\Prompt;



use Mw\Metamorph\Domain\Model\MorphCreationData;
use Mw\Metamorph\Io\OutputInterface;


class MorphCreationDataPrompt implements MorphCreationData
{


    /**
     * @var OutputInterface
     */
    protected $output;



    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }



    /**
     * @return string
     */
    public function getSourceDirectory()
    {
        $this->output->outputFormatted('Please enter the path to the root directory of the TYPO3 CMS site that you want to migrate');
        $this->output->output('<comment>Source Directory</comment>: ');

        $sourceDirectory = readline();
        return $sourceDirectory;
    }



    /**
     * @return array
     */
    public function getExtensionPatterns()
    {
        $this->output->outputFormatted('Please enter a list of regular expressions that extensions keys should match to be converted. You can enter multiple patterns in sequence; editing will stop when you insert an empty pattern. When you specify no pattern at all, all extensions will be converted.');
        $lastInput = NULL;
        $patterns  = [];

        while ($lastInput !== '')
        {
            if ($lastInput)
            {
                $patterns[] = $lastInput;
            }
            $this->output->output('<comment>Enter regex</comment> [%s]: ', [implode(', ', $patterns)]);
            $lastInput = readline();
        }

        return $patterns;
    }



    /**
     * @return bool
     */
    public function isKeepingTableStructure()
    {
        $this->output->outputFormatted('Do you want to re-use the existing table structure? If you choose "yes", Metamorph will use the existing table structures and enrich your doctrine models with the respective annotations.');
        return $this->promptBoolean('Re-use existing tables');
    }



    /**
     * @return bool
     */
    public function isAggressivelyRefactoringPiBaseExtensions()
    {
        $this->output->outputFormatted('Do you want to perform additional refactorings on piBase extensions? Please note that this might be dangerous.');
        return $this->promptBoolean('Aggressive refactoring');
    }



    /**
     * @return string
     */
    public function getVersionControlSystem()
    {
        $this->output->outputFormatted('Do you want metamorph to track changes to your package using a version control system?');
        $this->promptChoice('Version control system', ['git', 'none']);
    }



    private function promptChoice($prompt, array $allowedValues)
    {
        $input = NULL;
        while (FALSE == in_array($input, $allowedValues))
        {
            if ($input !== NULL)
            {
                $this->output->outputLine('<error>Please enter either one of "%s"!</error>', [implode(', ', $allowedValues)]);
            }
            $this->output->output('<comment>%s</comment> [%s]: ', [$prompt, implode(',', $allowedValues)]);
            $input = readline();
        }

        return $input;
    }



    private function promptBoolean($prompt)
    {
        return $this->promptChoice($prompt, ['y', 'n']);
    }
}