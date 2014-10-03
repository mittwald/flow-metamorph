<?php
namespace Mw\Metamorph\Domain\Model\State;


class ResourceMapping
{



    /**
     * @var string
     */
    protected $sourceFile;


    /**
     * @var string
     */
    protected $targetFile;


    /**
     * @var string
     */
    protected $package;



    public function __construct($sourceFile, $targetFile, $package)
    {
        $this->package    = $package;
        $this->sourceFile = $sourceFile;
        $this->targetFile = $targetFile;
    }



    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }



    /**
     * @return string
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }



    /**
     * @return string
     */
    public function getTargetFile()
    {
        return $this->targetFile;
    }



}