<?php
namespace Mw\Metamorph\Domain\Model\Definition;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Fact\EelFact;
use TYPO3\Flow\Annotations as Flow;

/**
 * A lookup container for custom facts.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\Definition
 *
 * @Flow\Scope("singleton")
 */
class FactContainer {

	/**
	 * All known fact definitions, as map of fact names to objects.
	 * @var Fact[]
	 */
	protected $factObjects = [];

	/**
	 * @var array
	 * @Flow\InjectSettings(path="facts")
	 */
	protected $factSettings;

	/**
	 * @var string
	 * @Flow\InjectSettings(path="defaults.factNamespace")
	 */
	protected $defaultNamespace;

	public function initializeObject() {
		foreach ($this->factSettings as $factName => $options) {
			if (array_key_exists('class', $options)) {
				$className = $options['class'];
				if (FALSE === class_exists($className)) {
					$className = $this->defaultNamespace . $className;
				}

				$this->factObjects[$factName] = new $className();
			} elseif (array_key_exists('expr', $options)) {
				$this->factObjects[$factName] = new EelFact($options['expr']);
			}
		}
	}

	/**
	 * Retrieves a fact by name from the fact container.
	 *
	 * If the fact is undefined, a special `NullFact` will be returned which always evaluates to NULL.
	 *
	 * @param string $factName The fact name
	 * @return Fact The fact object
	 */
	public function getFact($factName) {
		if (array_key_exists($factName, $this->factObjects)) {
			return $this->factObjects[$factName];
		}

		return new NullFact();
	}
}