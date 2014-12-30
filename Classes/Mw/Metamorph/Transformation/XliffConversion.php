<?php
namespace Mw\Metamorph\Transformation;

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ResourceMapping;
use Mw\Metamorph\Domain\Model\State\ResourceMappingContainer;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Files;

class XliffConversion extends AbstractTransformation implements Progressible {

	use ProgressibleTrait;

	/**
	 * @var PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state, OutputInterface $out) {
		$resourceMappingContainer = $configuration->getResourceMappingContainer();

		$locallangFiles = $this->findLocallangXmlFiles($resourceMappingContainer);
		$xliffFileCount = 0;

		$this->startProgress('Converting LL files', count($locallangFiles));
		foreach ($locallangFiles as $sourceFile => $data) {
			$xliffFileCount += count($data['languages']);
			$this->processLocallangFile($sourceFile, $data['languages'], $data['mapping']);
			$this->advanceProgress();
		}
		$this->finishProgress();

		$this->log(
			'Converted <comment>' . count($locallangFiles) . '</comment> locallang files into <comment>' .
			$xliffFileCount . '</comment> XLIFF files.'
		);
	}

	private function processLocallangFile($filename, array $languages, ResourceMapping $mapping) {
		$package = $this->packageManager->getPackage($mapping->getPackage());

		$stylesheet = new \DOMDocument();
		$stylesheet->load('resource://Mw.Metamorph/Private/Xslt/LocallangToXliff.xsl');

		$document = new \DOMDocument();
		$document->load($filename);

		$processor = new \XSLTProcessor();
		$processor->setParameter('metamorph', 'package', $mapping->getPackage());
		$processor->setParameter('metamorph', 'date', (new \DateTime())->format('c'));
		$processor->importStylesheet($stylesheet);

		foreach ($languages as $language) {
			$processor->setParameter('metamorph', 'language', $language);

			$targetDir  = Files::concatenatePaths([$package->getPackagePath(), dirname($mapping->getTargetFile())]);
			$targetFile = str_replace('.xml', '.xlf', basename($mapping->getTargetFile()));

			if ($language !== 'default') {
				$targetFile = $language . '.' . $targetFile;
			}

			$targetPath = Files::concatenatePaths([$targetDir, $targetFile]);

			$converted               = $processor->transformToDoc($document);
			$converted->formatOutput = TRUE;
			$converted->save($targetPath);
		}
	}

	private function findLocallangXmlFiles(ResourceMappingContainer $resourceMappingContainer) {
		$files = [];

		foreach ($resourceMappingContainer->getResourceMappings() as $resourceMapping) {
			if (substr($resourceMapping->getTargetFile(), -4) !== '.xml') {
				continue;
			}

			try {
				$doc = new \DOMDocument();
				$doc->load($resourceMapping->getSourceFile());

				$xpath = new \DOMXPath($doc);
				if (NULL === ($rootElements = $xpath->query('/T3locallang'))) {
					continue;
				}

				if (NULL === ($languageKeys = $xpath->query('data/languageKey', $rootElements->item(0)))) {
					continue;
				}

				$knownLanguageKeys = [];
				foreach ($languageKeys as $languageKey) {
					/** @noinspection PhpUndefinedFieldInspection */
					$knownLanguageKeys[] = $xpath->query('@index', $languageKey)->item(0)->value;
				}

				$files[$resourceMapping->getSourceFile()] = [
					'languages' => $knownLanguageKeys,
					'mapping'   => $resourceMapping
				];
			} catch (\Exception $e) {
				continue;
			}
		}

		return $files;
	}
}