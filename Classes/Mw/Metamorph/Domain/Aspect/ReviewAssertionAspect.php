<?php
namespace Mw\Metamorph\Domain\Aspect;

use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ReviewableTrait;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Aspect
 *
 * @Flow\Aspect
 */
class ReviewAssertionAspect {

	/**
	 * @Flow\Pointcut("within(Mw\Metamorph\Transformation\Transformation) && !classAnnotatedWith(Mw\Metamorph\Annotations\SkipPackageReview) && method(.*->execute())")
	 */
	public function packageMapReviewPointcut() { }

	/**
	 * @Flow\Pointcut("within(Mw\Metamorph\Transformation\Transformation) && !classAnnotatedWith(Mw\Metamorph\Annotations\SkipClassReview) && method(.*->execute())")
	 */
	public function classMapReviewPointcut() { }

	/**
	 * @Flow\Pointcut("within(Mw\Metamorph\Transformation\Transformation) && !classAnnotatedWith(Mw\Metamorph\Annotations\SkipResourceReview) && method(.*->execute())")
	 */
	public function resourceMapReviewPointcut() { }

	/**
	 * @param JoinPointInterface $joinPoint
	 * @throws \Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException
	 *
	 * @Flow\Before("Mw\Metamorph\Domain\Aspect\ReviewAssertionAspect->packageMapReviewPointcut")
	 */
	public function validatePackageMapReview(JoinPointInterface $joinPoint) {
		$this->assertReviewableIsReviewed(
			$joinPoint,
			function (MorphConfiguration $config) { return $config->getPackageMappingContainer(); }
		);
	}

	/**
	 * @param JoinPointInterface $joinPoint
	 * @throws \Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException
	 *
	 * @Flow\Before("Mw\Metamorph\Domain\Aspect\ReviewAssertionAspect->classMapReviewPointcut")
	 */
	public function validateClassMapReview(JoinPointInterface $joinPoint) {
		$this->assertReviewableIsReviewed(
			$joinPoint,
			function (MorphConfiguration $config) { return $config->getClassMappingContainer(); }
		);
	}

	/**
	 * @param JoinPointInterface $joinPoint
	 * @throws \Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException
	 *
	 * @Flow\Before("Mw\Metamorph\Domain\Aspect\ReviewAssertionAspect->resourceMapReviewPointcut")
	 */
	public function validateResourceMapReview(JoinPointInterface $joinPoint) {
		$this->assertReviewableIsReviewed(
			$joinPoint,
			function (MorphConfiguration $config) { return $config->getResourceMappingContainer(); }
		);
	}

	private function assertReviewableIsReviewed(JoinPointInterface $joinPoint, callable $getter) {
		$configuration = array_values($joinPoint->getMethodArguments())[0];
		if ($configuration instanceof MorphConfiguration) {
			/** @var ReviewableTrait $reviewable */
			$reviewable = call_user_func_array($getter, [$configuration]);
			$reviewable->assertReviewed();
		}
	}

}