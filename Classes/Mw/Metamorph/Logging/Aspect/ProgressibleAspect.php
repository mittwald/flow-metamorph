<?php
namespace Mw\Metamorph\Logging\Aspect;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Logging\LoggingWrapper;
use Mw\Metamorph\View\ProgressView;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;

/**
 * Aspects that handlers rendering of transformation progressions.
 *
 * @package    Mw\Metamorph
 * @subpackage Logging\Aspect
 *
 * @Flow\Aspect
 */
class ProgressibleAspect {

	/**
	 * @var LoggingWrapper
	 * @Flow\Inject
	 */
	protected $loggingWrapper;

	/**
	 * @var ProgressView
	 * @Flow\Inject(lazy=false)
	 */
	protected $view;

	/**
	 * @param JoinPointInterface $joinPoint
	 * @Flow\Before("within(Mw\Metamorph\Transformation\Progressible) && method(.*->startProgress())")
	 */
	public function addProgressViewAdvice(JoinPointInterface $joinPoint) {
		$addListenerFn = [$joinPoint->getProxy(), 'addProgressListener'];

		if (is_callable($addListenerFn)) {
			call_user_func($addListenerFn, $this->view);
		}
	}

}