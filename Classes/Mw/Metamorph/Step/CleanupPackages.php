<?php
namespace Mw\Metamorph\Step;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\TargetPackageCleanupEvent;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\AbstractTransformation;
use TYPO3\Flow\Annotations as Flow;

class CleanupPackages extends AbstractTransformation {

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		foreach ($configuration->getPackageMappingContainer()->getPackageMappings() as $packageMapping) {
			$packageKey = $packageMapping->getPackageKey();
			if ($this->packageManager->isPackageAvailable($packageKey)) {
				$this->log('PKG:<comment>%s</comment>: <fg=cyan>present</fg=cyan>', [$packageKey]);
				$this->emitTargetPackageCleanupEvent(
					new TargetPackageCleanupEvent($configuration, $this->packageManager->getPackage($packageKey))
				);
			} else {
				$this->log('PKG:<comment>%s</comment>: <fg=green>not present</fg=green>', [$packageKey]);
			}
		}
	}

	/**
	 * @param TargetPackageCleanupEvent $event
	 * @Event\Event
	 */
	protected function emitTargetPackageCleanupEvent(TargetPackageCleanupEvent $event) { }
}