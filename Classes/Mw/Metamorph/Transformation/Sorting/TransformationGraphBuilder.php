<?php
namespace Mw\Metamorph\Transformation\Sorting;

/**
 * Builds the transformation graph from the package settings.
 *
 * @package Mw\Metamorph
 * @subpackage Transformation\Sorting
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
		$nodes = [];
		foreach ($this->settings as $name => $configuration) {
			$settings     = isset($configuration['settings']) ? $configuration['settings'] : [];
			$nodes[$name] = new TransformationNode($name, $configuration['name'], $settings);
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