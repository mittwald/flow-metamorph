<?php
namespace Mw\Metamorph\Transformation\Sorting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2015 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

/**
 * Helper class for sorting a transformation graph.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Sorting
 */
interface TransformationSorter {

	/**
	 * Sorts a transformation graph.
	 *
	 * @param TransformationNode[] $nodes The node list to be sorted
	 * @return TransformationNode[] The sorted node list
	 */
	public function sort(array $nodes);

}