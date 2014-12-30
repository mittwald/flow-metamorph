<?php
namespace Mw\Metamorph\Transformation\ClassNameConversion;

class ExtbaseNamespacedConversionStrategy extends AbstractExtbaseConversionStrategy {

	protected function getNamespaceSeparator() {
		return '\\';
	}
}