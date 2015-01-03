<?php
namespace Mw\Metamorph\Transformation\Sorting;

interface TransformationSorter {

	/**
	 * @param array $nodes
	 * @return TransformationNode[]
	 */
	public function sort(array $nodes);

}