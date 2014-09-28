<?php
namespace Mw\Metamorph\Scm\Backend;



interface ScmBackendInterface
{



    public function initialize($directory);



    public function commit($directory, $message, array $files = []);



}