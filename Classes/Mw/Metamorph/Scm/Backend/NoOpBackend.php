<?php
namespace Mw\Metamorph\Scm\Backend;



class NoOpBackend implements ScmInterface
{


    public function initialize($directory)
    {
    }



    public function commit($directory, $message)
    {
    }
}