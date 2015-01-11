<?php
namespace Mw\Metamorph\Step\ClassNameConversion;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\PositionalArraySorter;

class CompoundConversionStrategy implements ClassNameConversionStrategy {

	/**
	 * @var array
	 * @Flow\Inject(setting="classNameStrategies")
	 */
	protected $strategyConfiguration;

	/**
	 * @var ClassNameConversionStrategy[]
	 */
	protected $strategies = [];

	public function initializeObject() {
		$sortedStrategyConfiguration = (new PositionalArraySorter($this->strategyConfiguration))->toArray();
		foreach ($sortedStrategyConfiguration as $strategyConfigurationItem) {
			$class = $strategyConfigurationItem['name'];
			if (FALSE === class_exists($class)) {
				$class = __NAMESPACE__ . '\\' . $class;
			}

			$this->strategies[] = new $class();
		}
	}

	/**
	 * @param string $className    A TYPO3 CMS class name
	 * @param string $extensionKey The extension key
	 * @return boolean TRUE when this strategy can handle this class name
	 */
	public function accept($className, $extensionKey) {
		foreach ($this->strategies as $strategy) {
			if ($strategy->accept($className, $extensionKey)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param string $namespaceRoot The PSR-0 namespace root
	 * @param string $className     A TYPO3 CMS class name
	 * @param string $filename      The original file name
	 * @param string $extensionKey  The extension key
	 * @return string An appropriate class name for the migrated TYPO3 Flow package
	 */
	public function convertClassName($namespaceRoot, $className, $filename, $extensionKey) {
		foreach ($this->strategies as $strategy) {
			if ($strategy->accept($className, $extensionKey)) {
				return $strategy->convertClassName($namespaceRoot, $className, $filename, $extensionKey);
			}
		}
		return self::PASS;
	}
}