<?php
namespace Mw\Metamorph\Domain\Event;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;


class MorphConfigurationCreatedEvent
{



    /** @var MorphConfiguration */
    private $morphConfiguration;


    /** @var MorphCreationDto */
    private $options;



    public function __construct(MorphConfiguration $morphConfiguration, MorphCreationDto $options)
    {
        $this->morphConfiguration = $morphConfiguration;
        $this->options            = $options;
    }



    /**
     * @return MorphConfiguration
     */
    public function getMorphConfiguration()
    {
        return $this->morphConfiguration;
    }



    /**
     * @return MorphCreationDto
     */
    public function getOptions()
    {
        return $this->options;
    }



}