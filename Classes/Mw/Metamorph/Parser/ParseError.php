<?php
namespace Mw\Metamorph\Parser;

use Exception;

class ParseError extends \Exception {

	protected $filename;

	public function __construct($message, $filename) {
		parent::__construct($message . ' in ' . $filename);
	}

	public function getFilename() {
		return $this->filename;
	}



}