<?php
namespace Mw\Metamorph\Scm\Backend;


use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;


class GitBackend implements ScmBackendInterface
{



    public function initialize($directory)
    {
        $repo = Admin::init($directory, FALSE);
        $repo->run('config', ['user.name', 'Metamorph']);
        $repo->run('config', ['user.email', 'metamorph@localhost']);

        $repo->run('add', ['.']);
        $repo->run('commit', ['-m', 'Initialize new metamorph project.']);

        $references = $repo->getReferences();
        $references->createBranch('metamorph', 'master');
    }



    public function commit($directory, $message, array $files = [])
    {
        if ($this->isModified($directory))
        {
            $repo = new Repository($directory);
            $work = $repo->getWorkingCopy();

            $work->checkout('metamorph');

            $files = count($files) ? $files : ['.'];

            $repo->run('add', $files);
            $repo->run('commit', ['-m', $message]);

            $work->checkout('master');

            $repo->run('merge', ['metamorph']);
        }
    }



    public function isModified($directory)
    {
        $repo = new Repository($directory);
        $diff = $repo->getWorkingCopy()->getDiffPending();

        return count($diff->getFiles()) > 0;
    }


}