<?php
namespace Mw\Metamorph\Io;


use Symfony\Component\Console\Output\OutputInterface;


interface DecoratedOutputInterface extends OutputInterface
{



    public function writeFormatted($text);

}