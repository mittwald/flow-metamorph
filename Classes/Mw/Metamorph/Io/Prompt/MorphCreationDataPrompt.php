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
        $this->output->output('<u>Source Directory</u>: ');

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
        // TODO: Implement isKeepingTableStructure() method.
    }



    /**
     * @return bool
     */
    public function isAggressivelyRefactoringPiBaseExtensions()
    {
        // TODO: Implement isAggressivelyRefactoringPiBaseExtensions() method.
    }
}