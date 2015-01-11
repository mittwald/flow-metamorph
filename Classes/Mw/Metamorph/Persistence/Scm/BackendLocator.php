<?php
namespace Mw\Metamorph\Persistence\Scm;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Persistence\Scm\Backend\GitBackend;
use Mw\Metamorph\Persistence\Scm\Backend\NoOpBackend;
use Mw\Metamorph\Persistence\Scm\Backend\ScmBackendInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class BackendLocator {

	/**
	 * @var PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * @param string $backendIdentifier
	 * @return \Mw\Metamorph\Persistence\Scm\Backend\ScmBackendInterface
	 */
	public function getBackendByIdentifier($backendIdentifier) {
		switch (strtolower($backendIdentifier)) {
			case 'git':
				return new GitBackend();
			default:
				return new NoOpBackend();
		}
	}

	/**
	 * @param MorphConfiguration $configuration
	 * @return \Mw\Metamorph\Persistence\Scm\Backend\ScmBackendInterface
	 */
	public function getBackendByConfiguration(MorphConfiguration $configuration) {
		$package = $this->packageManager->getPackage($configuration->getName());
		if (file_exists(Files::concatenatePaths([$package->getPackagePath(), '.git']))) {
			return new GitBackend();
		}

		return new NoOpBackend();
	}

}