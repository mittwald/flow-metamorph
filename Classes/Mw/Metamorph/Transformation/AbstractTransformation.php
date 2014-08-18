<?php
namespace Mw\Metamorph\Transformation;


use TYPO3\Flow\Mvc\ResponseInterface;


abstract class AbstractTransformation implements Transformation
{



    protected $settings;



    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }



}