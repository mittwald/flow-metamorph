<?php
namespace Mw\Metamorph\Parser;

interface ParserInterface {

	public function parseFile($filename);

	public function parseCode($code);

}