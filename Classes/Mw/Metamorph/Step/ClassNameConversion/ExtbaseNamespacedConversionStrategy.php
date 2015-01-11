<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

class ExtbaseNamespacedConversionStrategy extends AbstractExtbaseConversionStrategy {

	protected function getNamespaceSeparator() {
		return '\\';
	}
}