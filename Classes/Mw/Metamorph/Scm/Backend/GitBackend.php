<?php
namespace Mw\Metamorph\Scm\Backend;


use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;


class GitBackend implements ScmInterface
{


    public function initialize($directory)
    {
        Admin::init($directory, FALSE);
    }



    public function commit($directory, $message)
    {
        $repo = new Repository($directory);
        $repo->run('add', ['.']);
        $repo->run('commit', ['-m', $message]);
    }
}