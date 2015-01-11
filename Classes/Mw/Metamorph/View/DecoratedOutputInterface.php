<?php
namespace Mw\Metamorph\View;

use Symfony\Component\Console\Output\OutputInterface;

interface DecoratedOutputInterface extends OutputInterface {

	public function writeFormatted($text, $indent = 0);

}