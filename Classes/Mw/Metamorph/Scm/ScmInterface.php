<?php
namespace Mw\Metamorph\Scm;



interface ScmInterface
{


    public function initialize($directory);



    public function commit($directory, $message);
} 