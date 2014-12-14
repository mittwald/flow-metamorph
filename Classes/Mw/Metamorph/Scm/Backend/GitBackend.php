<?php
namespace Mw\Metamorph\Scm\Backend;

use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;
use Gitonomy\Git\WorkingCopy;

class GitBackend implements ScmBackendInterface {

	/**
	 * @var Repository[]
	 */
	private $repositories = [];

	public function initialize($directory) {
		$repo = Admin::init($directory, FALSE);
		$repo->run('config', ['user.name', 'Metamorph']);
		$repo->run('config', ['user.email', 'metamorph@localhost']);

		$repo->run('add', ['.']);
		$repo->run('commit', ['-m', 'Initialize new metamorph project.']);

		$references = $repo->getReferences();
		$references->createBranch('metamorph', 'master');
	}

	public function commit($directory, $message, array $files = []) {
		if (FALSE === $this->isModified($directory)) {
			return;
		}

		$repo = $this->getRepository($directory);
		$work = $repo->getWorkingCopy();

		if (count($files) > 0) {
			$files = $this->getActuallyModifiedFiles($directory, $files, $work, $repo);
			if (0 === count($files)) {
				return;
			}
		} else {
			$files = ['.'];
		}

		$work->checkout('metamorph');

		$repo->run('add', $files);
		$repo->run('commit', ['-m', $message]);

		$work->checkout('master');

		$repo->run('merge', ['metamorph']);
	}

	public function isModified($directory) {
		$work      = $this->getRepository($directory)->getWorkingCopy();
		$diff      = $work->getDiffPending();
		$untracked = $work->getUntrackedFiles();

		return (count($diff->getFiles()) + count($untracked)) > 0;
	}

	/**
	 * @param string $directory
	 * @return Repository
	 */
	private function getRepository($directory) {
		if (FALSE === array_key_exists($directory, $this->repositories)) {
			$this->repositories[$directory] = new Repository($directory);
		}

		return $this->repositories[$directory];
	}

	/**
	 * @param             $directory
	 * @param array       $files
	 * @param WorkingCopy $work
	 * @return array
	 */
	private function getActuallyModifiedFiles($directory, array $files, WorkingCopy $work, Repository $repo) {
		$files = array_map(
			function ($file) use ($directory) { return str_replace(rtrim($directory, '/') . '/', '', $file); },
			$files
		);

		// Needed until https://github.com/gitonomy/gitlib/pull/72 is merged
		$getUntracked = function () use ($repo) {
			$lines = explode("\0", $repo->run('status', array('--porcelain', '--untracked-files=all', '-z')));
			$lines = array_filter($lines, function ($l) { return substr($l, 0, 3) === '?? '; });
			$lines = array_map(function ($l) { return substr($l, 3); }, $lines);
			return $lines;
		};

		$filter = function ($file) use ($directory, $work, $getUntracked) {
			$modified  = $work->getDiffPending()->getFiles();
			$untracked = $getUntracked();

			return in_array($file, $modified) || in_array($file, $untracked);
		};

		$files = array_filter($files, $filter);
		return $files;
	}

}