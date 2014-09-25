<?php
namespace Mw\Metamorph\Scm;



class NoOp implements ScmInterface
{


    public function initialize($directory)
    {
    }



    public function commit($directory, $message)
    {
    }
}