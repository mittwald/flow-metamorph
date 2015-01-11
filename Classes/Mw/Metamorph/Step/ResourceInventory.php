<?php
namespace Mw\Metamorph\Step;

use Mw\Metamorph\Annotations as Metamorph;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Model\State\ResourceMapping;
use Mw\Metamorph\Domain\Model\State\ResourceMappingContainer;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\AbstractTransformation;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Transformation
 *
 * @Metamorph\SkipResourceReview
 */
class ResourceInventory extends AbstractTransformation {

	/**
	 * @var array
	 * @Flow\Inject(setting="resourceExtensions")
	 */
	protected $resourceExtensions;

	/**
	 * @var MorphConfigurationRepository
	 * @Flow\Inject
	 */
	protected $morphRepository;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$packageMappingContainer  = $configuration->getPackageMappingContainer();
		$resourceMappingContainer = $configuration->getResourceMappingContainer();

		foreach ($packageMappingContainer->getPackageMappings() as $packageMapping) {
			$this->loadResourcesForExtension($packageMapping, $resourceMappingContainer);
		}

		$this->log(
			'Found <comment>' . count($resourceMappingContainer->getResourceMappings()) . '</comment> resource files.'
		);
		$this->morphRepository->update($configuration);
	}

	private function loadResourcesForExtension(
		PackageMapping $packageMapping,
		ResourceMappingContainer $resourceMappingContainer
	) {
		$directoryIterator = new \RecursiveDirectoryIterator($packageMapping->getFilePath());
		$iteratorIterator  = new \RecursiveIteratorIterator($directoryIterator);
		$regexIterator     = new \RegexIterator(
			$iteratorIterator,
			$this->buildFileRegex(),
			\RecursiveRegexIterator::GET_MATCH
		);

		foreach ($regexIterator as $match) {
			$this->processResource($match[0], $packageMapping, $resourceMappingContainer);
		}
	}

	private function processResource(
		$absoluteSourceFile,
		PackageMapping $packageMapping,
		ResourceMappingContainer $resourceMappingContainer
	) {
		$relativePath = substr($absoluteSourceFile, strlen($packageMapping->getFilePath()) + 1);
		$targetPath   = NULL;

		if ('Resources/' === substr($relativePath, 0, 10)) {
			$targetPath = $relativePath;
		} else {
			$extension = pathinfo($absoluteSourceFile, PATHINFO_EXTENSION);
			foreach ($this->resourceExtensions as $purpose => $extensionsByPurpose) {
				if (in_array($extension, $extensionsByPurpose['extensions'])) {
					$targetPath = 'Resources/' . $extensionsByPurpose['targetPath'] . '/' . basename($relativePath);
					break;
				}
			}
		}

		$resourceMapping = new ResourceMapping($absoluteSourceFile, $targetPath, $packageMapping->getPackageKey());
		$resourceMappingContainer->addResourceMapping($resourceMapping);
	}

	private function buildFileRegex() {
		$extensions = [];
		foreach ($this->resourceExtensions as $purpose => $extensionsByPurpose) {
			$extensions = array_merge($extensions, $extensionsByPurpose['extensions']);
		}

		return '/^.+\.(' . implode('|', $extensions) . ')$/i';
	}
}
