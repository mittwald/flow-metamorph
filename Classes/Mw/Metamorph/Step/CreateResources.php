<?php
namespace Mw\Metamorph\Step;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\AbstractTransformation;
use Mw\Metamorph\Transformation\Progressible;
use Mw\Metamorph\Transformation\ProgressibleTrait;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Files;

class CreateResources extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$this->startProgress(
			'Migrating resources',
			count($configuration->getResourceMappingContainer()->getResourceMappings())
		);

		foreach ($configuration->getResourceMappingContainer()->getResourceMappings() as $resourceMapping) {
			$targetFilePath = $resourceMapping->getTargetFile();
			$targetDirectory = dirname($targetFilePath);

			Files::createDirectoryRecursively($targetDirectory);
			copy($resourceMapping->getSourceFile(), $targetFilePath);

			$this->advanceProgress();
		}

		$this->finishProgress();
	}

}
