<?php
namespace Mw\Metamorph\Transformation\Sorting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Transformation\NoopTransformation;
use TYPO3\Flow\Annotations as Flow;

/**
 * Builds the transformation graph from the package settings.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Sorting
 *
 * @Flow\Scope("prototype")
 */
class TransformationGraphBuilder {

	/** @var array */
	private $settings;

	/**
	 * Creates a new transformation graph builder.
	 *
	 * @param array $transformationSettings The transformation settings
	 */
	public function __construct(array $transformationSettings) {
		$this->settings = $transformationSettings;
	}

	/**
	 * Builds the transformation graph.
	 *
	 * @return TransformationNode[] The transformation graph as list of nodes.
	 */
	public function build() {
		/** @var TransformationNode[] $nodes */
		$nodes = [
			'finalize' => new TransformationNode('finalize', NoopTransformation::class)
		];

		foreach ($this->settings as $name => $configuration) {
			$settings = isset($configuration['settings']) ? $configuration['settings'] : [];
			$node     = new TransformationNode($name, $configuration['name'], $settings);

			if (!isset($configuration['dependsOn']) || !in_array('finalize', $configuration['dependsOn'])) {
				$node->addSuccessor($nodes['finalize']);
			}

			$nodes[$name] = $node;
		}

		foreach ($this->settings as $name => $configuration) {
			if (isset($configuration['dependsOn'])) {
				foreach ($configuration['dependsOn'] as $dependencyName) {
					$nodes[$name]->addPredecessor($nodes[$dependencyName]);
				}
			}

			if (isset($configuration['requiredBy'])) {
				foreach ($configuration['requiredBy'] as $reverseDependencyName) {
					$nodes[$reverseDependencyName]->addPredecessor($nodes[$name]);
				}
			}
		}

		return $nodes;
	}

}