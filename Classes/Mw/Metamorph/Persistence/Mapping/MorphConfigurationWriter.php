<?php
namespace Mw\Metamorph\Persistence\Mapping;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\PatternExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\UnionMatcher;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Persistence\Mapping\State\ClassMappingContainerWriter;
use Mw\Metamorph\Persistence\Mapping\State\ContainerWriterInterface;
use Mw\Metamorph\Persistence\Mapping\State\PackageMappingContainerWriter;
use Mw\Metamorph\Persistence\Mapping\State\ResourceMappingContainerWriter;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;
use TYPO3\Flow\Package\PackageInterface;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Files;

/**
 * Writes morph configurations to disk.
 *
 * @package    Mw\Metamorph
 * @subpackage Persistence\Mapping
 *
 * @Flow\Scope("singleton")
 */
class MorphConfigurationWriter {

	/**
	 * @var PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * @var ContainerWriterInterface[]
	 */
	protected $writers = [];

	/**
	 * @var array
	 * @Flow\Inject(setting="containers")
	 */
	protected $containerConfiguration;

	public function initializeObject() {
		foreach ($this->containerConfiguration as $name => $configuration) {
			$this->writers[$name] = new $configuration['writer']();
		}
	}

	public function createMorph(MorphConfiguration $morphConfiguration) {
		$metaData = new MetaData($morphConfiguration->getName());
		$package  = $this->packageManager->createPackage($morphConfiguration->getName(), $metaData);
		$morphConfiguration->setPackage($package);

		$this->writeMorph($package, $morphConfiguration);
	}

	public function updateMorph(MorphConfiguration $morphConfiguration) {
		$package = $this->packageManager->getPackage($morphConfiguration->getName());
		$this->writeMorph($package, $morphConfiguration);
	}

	public function removeMorph(MorphConfiguration $morphConfiguration) {
		$this->packageManager->deletePackage($morphConfiguration->getName());
	}

	private function writeMorph(PackageInterface $package, MorphConfiguration $morphConfiguration) {
		$morphData = [
			'sourceDirectory'       => $morphConfiguration->getSourceDirectory(),
			'extensions'            => $this->exportExtensionMatcher($morphConfiguration->getExtensionMatcher()),
			'tableStructureMode'    => $morphConfiguration->getTableStructureMode(),
			'pibaseRefactoringMode' => $morphConfiguration->getPibaseRefactoringMode()
		];

		$configurationPath = $package->getConfigurationPath();
		$morphPath         = Files::concatenatePaths([$configurationPath, 'Metamorph', 'Morph.yml']);

		Files::createDirectoryRecursively(dirname($morphPath));
		file_put_contents($morphPath, Yaml::dump($morphData));

		foreach ($this->writers as $writer) {
			$writer->writeMorphContainer($morphConfiguration);
		}
	}

	private function exportExtensionMatcher(ExtensionMatcher $matcher) {
		if ($matcher instanceof PatternExtensionMatcher) {
			return ['pattern' => $matcher->getPattern()];
		} else if ($matcher instanceof UnionMatcher) {
			return array_map(array($this, 'exportExtensionMatcher'), $matcher->getMatchers());
		}
		return NULL;
	}

} 