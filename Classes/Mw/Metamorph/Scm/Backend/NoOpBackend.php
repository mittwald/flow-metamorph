<?php
namespace Mw\Metamorph\Scm\Backend;

class NoOpBackend implements ScmBackendInterface {

	public function initialize($directory) { }

	public function commit($directory, $message, array $files = []) { }

	public function isModified($directory) { return FALSE; }

}