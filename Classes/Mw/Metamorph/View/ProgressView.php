<?php
namespace Mw\Metamorph\View;

use Mw\Metamorph\Logging\LoggingWrapper;
use Mw\Metamorph\Transformation\ProgressListener;
use Symfony\Component\Console\Helper\ProgressBar;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage View
 *
 * @Flow\Scope("singleton")
 */
class ProgressView implements ProgressListener {

	/**
	 * @var LoggingWrapper
	 * @Flow\Inject
	 */
	protected $loggingWrapper;

	/**
	 * @var ProgressBar
	 */
	private $progress;

	public function onProgressStart($message, $max) {
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

		$this->progress = $progress;
	}

	public function onProgressAdvance() {
		$this->progress->advance();
	}

	public function onProgressFinish() {
		$this->progress->finish();
		$this->loggingWrapper->write("\n");
	}
}