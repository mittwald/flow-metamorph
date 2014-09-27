<?php
namespace Mw\Metamorph\Domain\Model;



interface MorphCreationData
{



    /**
     * @return string
     */
    public function getSourceDirectory();



    /**
     * @return array
     */
    public function getExtensionPatterns();



    /**
     * @return bool
     */
    public function isKeepingTableStructure();



    /**
     * @return bool
     */
    public function isAggressivelyRefactoringPiBaseExtensions();

}