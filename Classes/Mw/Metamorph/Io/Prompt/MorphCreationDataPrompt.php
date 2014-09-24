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
        // TODO: Implement getExtensionPatterns() method.
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



    private function promptBoolean($prompt)
    {
        $input = NULL;
        while ($input !== 'n' && $input !== 'y')
        {
            if ($input !== NULL)
            {
                $this->output->outputLine('<error>Please enter either "y" or "n"!</error>');
            }
            $this->output->output('<comment>' . $prompt . '</comment> [y/n]: ');
            $input = readline();
        }

        return $input === 'y';
    }
}