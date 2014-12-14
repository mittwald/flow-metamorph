<?php
namespace Mw\Metamorph\Domain\Model\Extension;

class PatternExtensionMatcher implements ExtensionMatcher {

	private $pattern;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function match($extensionKey) {
		return preg_match($this->pattern, $extensionKey);
	}

	public function getPattern() {
		return $this->pattern;
	}

}