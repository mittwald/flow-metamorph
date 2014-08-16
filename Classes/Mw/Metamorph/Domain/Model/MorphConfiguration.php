<?php
namespace Mw\Metamorph\Domain\Model;


use Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher;


class MorphConfiguration
{



    private $name;


    private $sourceDirectory;


    /**
     * @var \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher
     */
    private $extensionMatcher;



    public function __construct($name, $sourceDirectory, ExtensionMatcher $extensionMatcher)
    {
        $this->name             = $name;
        $this->sourceDirectory  = $sourceDirectory;
        $this->extensionMatcher = $extensionMatcher;
    }



    /**
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }



    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



    /**
     * @return \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher
     */
    public function getExtensionMatcher()
    {
        return $this->extensionMatcher;
    }



} 