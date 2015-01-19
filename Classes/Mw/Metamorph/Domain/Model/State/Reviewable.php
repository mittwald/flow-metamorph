<?php
namespace Mw\Metamorph\Domain\Model\State;

/**
 * Interface definition for objects that need manual user-review.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 */
interface Reviewable {

	/**
	 * Asserts that the object has been user-reviewed.
	 *
	 * Will throw a HumanInterventionRequiredException when not.
	 *
	 * @return void
	 */
	public function assertReviewed();

	/**
	 * Determines whether the object has been user-reviewed.
	 *
	 * @return bool TRUE, when the object has been user-reviewed, otherwise FALSE.
	 */
	public function isReviewed();
}