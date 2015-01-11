<?php
namespace Mw\Metamorph\Persistence\Scm\Backend;

interface ScmBackendInterface {

	public function initialize($directory);

	public function commit($directory, $message, array $files = []);

	public function isModified($directory);

}