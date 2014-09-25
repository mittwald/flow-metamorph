<?php
namespace Mw\Metamorph\Domain\Model;


class DefaultMorphCreationData implements MorphCreationData
{


    /**
     * @return string
     */
    public function getSourceDirectory()
    {
        return '/var/www/typo3-site';
    }



    /**
     * @return array
     */
    public function getExtensionPatterns()
    {
        return [];
    }



    /**
     * @return bool
     */
    public function isKeepingTableStructure()
    {
        return FALSE;
    }



    /**
     * @return bool
     */
    public function isAggressivelyRefactoringPiBaseExtensions()
    {
        return FALSE;
    }



    /**
     * @return string
     */
    public function getVersionControlSystem()
    {
        return 'git';
    }


}