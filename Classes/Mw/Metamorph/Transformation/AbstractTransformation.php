<?php
namespace Mw\Metamorph\Transformation;


abstract class AbstractTransformation implements Transformation
{



    protected $settings;



    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }



    protected function log($message, array $arguments=[])
    {
    }



}