<?php
namespace Mw\Metamorph\Domain\Model\State;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;


trait Reviewable
{



    protected $reviewed = TRUE;


    /** @var MorphConfiguration */
    protected $configuration;



    public function assertReviewed()
    {
        if (FALSE === $this->reviewed)
        {
            $classComponents = explode('\\', get_class($this));
            $class           = array_pop($classComponents);

            $what = strtolower(trim(preg_replace(',[A-Z],', ' $0', $class)));
            $what = trim(str_replace(['container', 'proxy'], '', $what));

            throw new HumanInterventionRequiredException(
                'Please review the <comment>' . $what . '</comment> in the ' .
                '<comment>Configuration/Metamorph/Work</comment> directory of the <comment>' .
                $this->configuration->getName() . '</comment> package by changing the <comment>reviewed</comment> ' .
                'property to TRUE.'
            );
        }
    }



    public function isReviewed()
    {
        return $this->reviewed;
    }


}