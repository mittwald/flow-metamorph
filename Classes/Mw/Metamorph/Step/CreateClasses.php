<?php
namespace Mw\Metamorph\Step;

use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Step\ClassNameConversion\ClassToFilenameConversion;
use Mw\Metamorph\Transformation\AbstractTransformation;
use Mw\Metamorph\Transformation\Progressible;
use Mw\Metamorph\Transformation\ProgressibleTrait;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageInterface;
use TYPO3\Flow\Utility\Files;

class CreateClasses extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * @var MorphConfigurationRepository
	 * @Flow\Inject
	 */
	protected $morphRepository;

	/**
	 * @var ClassToFilenameConversion
	 * @Flow\Inject
	 */
	protected $classFilenameConversion;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$classMappingContainer = $configuration->getClassMappingContainer();
		$packageClassCount     = [];

		$this->startProgress('Migrating classes', count($classMappingContainer->getClassMappings()));

		foreach ($classMappingContainer->getClassMappings() as $classMapping) {
			if ($classMapping->getAction() !== ClassMapping::ACTION_MORPH) {
				continue;
			}

			$package      = $this->packageManager->getPackage($classMapping->getPackage());
			$source       = file_get_contents($classMapping->getSourceFile());
			$newClassName = new String($classMapping->getNewClassName());

			$relativeFilename = $newClassName->replace('\\', '/')->append('.php');
			$absoluteFilename = $this->classFilenameConversion->getAbsoluteFilename($relativeFilename, $package);

			Files::createDirectoryRecursively(dirname($absoluteFilename));

			file_put_contents($absoluteFilename, $source);

			if (!isset($packageClassCount[$package->getPackageKey()])) {
				$packageClassCount[$package->getPackageKey()] = 0;
			}
			$packageClassCount[$package->getPackageKey()]++;

			$classMapping->setTargetFile($absoluteFilename);
			$this->advanceProgress();
		}

		$this->finishProgress();
		$this->morphRepository->update($configuration);

		foreach ($packageClassCount as $package => $count) {
			$this->log('<comment>%d</comment> classes written to package <comment>%s</comment>.', [$count, $package]);
		}
	}
}
