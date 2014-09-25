<?php
namespace Mw\Metamorph\Scm;



class Git implements ScmInterface
{


    public function initialize($directory)
    {
        $command = 'git init ' . escapeshellarg($directory);
        exec($command, $output, $returnCode);

        if (0 !== $returnCode)
        {
            throw new \Exception('Could not execute "git init" in "' . $directory . '"');
        }
    }



    public function commit($directory, $message)
    {
        $oldWorkingDir = getcwd();

        chdir($directory);

    }
}