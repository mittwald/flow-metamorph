<?php
namespace Mw\Metamorph\Transformation;

interface ProgressListener {

	public function onProgressStart($message, $max);

	public function onProgressAdvance();

	public function onProgressFinish();

}