<?php
namespace Mw\Metamorph;

use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		/** @noinspection PhpIncludeInspection */
		require_once FLOW_PATH_PACKAGES . '/Libraries/nikic/php-parser/lib/bootstrap.php';
	}
}
