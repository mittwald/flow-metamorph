<?php
namespace Mw\Metamorph\Transformation;

interface Progressible {

	public function addProgressListener(ProgressListener $listener);

}