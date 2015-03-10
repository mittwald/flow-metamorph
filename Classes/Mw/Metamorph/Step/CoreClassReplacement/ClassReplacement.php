<?php
namespace Mw\Metamorph\Step\CoreClassReplacement;

interface ClassReplacement {

	public function replaceInComment($comment);

	public function replaceName($name);
}