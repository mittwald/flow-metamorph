<?php
namespace Mw\Metamorph\Domain\Event;


use Mw\Metamorph\Domain\Model\MorphConfiguration;


class MorphConfigurationFileModifiedEvent
{



    /**
     * @var MorphConfiguration
     */
    private $configuration;


    /**
     * @var string
     */
    private $relativeFilename;


    /**
     * @var string
     */
    private $purpose;



    public function __construct(MorphConfiguration $configuration, $relativeFilename, $purpose)
    {
        $this->configuration    = $configuration;
        $this->relativeFilename = $relativeFilename;
        $this->purpose          = $purpose;
    }



    /**
     * @return MorphConfiguration
     */
    public function getMorphConfiguration()
    {
        return $this->configuration;
    }



    /**
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }



    /**
     * @return string
     */
    public function getRelativeFilename()
    {
        return $this->relativeFilename;
    }


}