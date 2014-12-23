<?php
namespace Mw\Metamorph\Logging\Aspect;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Logging\LoggingWrapper;
use Symfony\Component\Console\Helper\ProgressBar;
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
	 * @param JoinPointInterface $joinPoint
	 * @return mixed
	 *
	 * @Flow\AfterReturning("method(.*->startProgress())")
	 */
	public function progressStartAdvice(JoinPointInterface $joinPoint) {
		list($message, $max) = array_values($joinPoint->getMethodArguments());

		$progress = new ProgressBar($this->loggingWrapper, $max);
		$progress->setFormat(
			$this->loggingWrapper->getNestingPrefix() . ($max
				? '%message:-20s% <comment>%current:4s%</comment>/<comment>%max:-4s%</comment> [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'
				: '%message:-20s% <comment>%current:4s%</comment>/<comment>%max:-4s%</comment> [%bar%]'
			)
		);
		$progress->setBarCharacter('<comment>=</comment>');
		$progress->setMessage($message);
		$progress->display();

		$joinPoint->getProxy()->__metamorphProgress = $progress;
	}

	/**
	 * @param JoinPointInterface $joinPoint
	 * @return mixed
	 *
	 * @Flow\AfterReturning("method(.*->advanceProgress())")
	 */
	public function progressAdvanceAdvice(JoinPointInterface $joinPoint) {
		if (isset($joinPoint->getProxy()->__metamorphProgress)) {
			$joinPoint->getProxy()->__metamorphProgress->advance();
		}
	}

	/**
	 * @param JoinPointInterface $joinPoint
	 * @return mixed
	 *
	 * @Flow\AfterReturning("method(.*->finishProgress())")
	 */
	public function progressFinishAdvice(JoinPointInterface $joinPoint) {
		if (isset($joinPoint->getProxy()->__metamorphProgress)) {
			$joinPoint->getProxy()->__metamorphProgress->finish();
		}
		$this->loggingWrapper->write("\n");
	}
} 