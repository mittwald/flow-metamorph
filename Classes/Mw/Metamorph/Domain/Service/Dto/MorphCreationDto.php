<?php
namespace Mw\Metamorph\Domain\Service\Dto;


/**
 * Data transfer object for passing morph creation parameters into the MorphService.
 *
 * @package    Mw\Metamorph
 * @subpackage \Domain\Service\Dto
 */
class MorphCreationDto
{



    /**
     * Source directory of TYPO3 CMS installation.
     * @var string
     */
    private $sourceDirectory;


    /**
     * List of regular expressions to match extension names.
     * @var array
     */
    private $extensionPatterns = [];


    /**
     * What to do with existing database structures.
     * @var string
     */
    private $tableStructureMode;


    /**
     * How aggressively to refactor piBase extensions.
     * @var string
     */
    private $pibaseRefactoringMode;



    /**
     * @return array
     */
    public function getExtensionPatterns()
    {
        return $this->extensionPatterns;
    }



    /**
     * @param array $extensionPatterns
     */
    public function setExtensionPatterns($extensionPatterns)
    {
        $this->extensionPatterns = $extensionPatterns;
    }



    /**
     * @return string
     */
    public function getPibaseRefactoringMode()
    {
        return $this->pibaseRefactoringMode;
    }



    /**
     * @param string $pibaseRefactoringMode
     */
    public function setPibaseRefactoringMode($pibaseRefactoringMode)
    {
        $this->pibaseRefactoringMode = $pibaseRefactoringMode;
    }



    /**
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }



    /**
     * @param string $sourceDirectory
     */
    public function setSourceDirectory($sourceDirectory)
    {
        $this->sourceDirectory = $sourceDirectory;
    }



    /**
     * @return string
     */
    public function getTableStructureMode()
    {
        return $this->tableStructureMode;
    }



    /**
     * @param string $tableStructureMode
     */
    public function setTableStructureMode($tableStructureMode)
    {
        $this->tableStructureMode = $tableStructureMode;
    }



}