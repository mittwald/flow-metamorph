<?php
namespace Mw\Metamorph\Step;

use Helmich\Scalars\Types\ArrayList;
use Helmich\Scalars\Types\String;
use Mw\Metamorph\Annotations as Metamorph;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Transformation\AbstractTransformation;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Transformation
 *
 * @Metamorph\SkipConfigurationValidation
 * @Metamorph\SkipPackageReview
 * @Metamorph\SkipClassReview
 * @Metamorph\SkipResourceReview
 */
class ExtensionInventory extends AbstractTransformation {

	/**
	 * @var MorphConfigurationRepository
	 * @Flow\Inject
	 */
	protected $morphRepository;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$rootDirectory     = $configuration->getSourceDirectory() . 'typo3conf/ext';
		$directoryIterator = new \DirectoryIterator($rootDirectory);
		$matcher           = $configuration->getExtensionMatcher();

		$packageMappingContainer = $configuration->getPackageMappingContainer();
		$foundExtensionKeys      = [];

		foreach ($directoryIterator as $directoryInfo) {
			/** @var \DirectoryIterator $directoryInfo */
			if (!$directoryInfo->isDir() || !file_exists($directoryInfo->getPathname() . '/ext_emconf.php')) {
				continue;
			}

			$extensionKey = $directoryInfo->getBasename();

			if ($matcher->match($extensionKey)) {
				$this->log('EXT:<comment>%s</comment>: <info>FOUND</info>', [$extensionKey]);

				$mapping = new PackageMapping($directoryInfo->getPathname(), $extensionKey);
				$mapping->setPackageKey($this->convertExtensionKeyToPackageName($extensionKey));

				$this->enrichPackageDataWithEmConfData($mapping);

				$foundExtensionKeys[] = $extensionKey;
				$packageMappingContainer->addPackageMapping($mapping);
			} else {
				$this->log('EXT:<comment>%s</comment>: <fg=cyan>IGNORING</fg=cyan>', [$extensionKey]);
			}
		}

		// Remove extensions that are defined in the package map, but not present anymore
		// in the source directory.
		foreach ($packageMappingContainer->getPackageMappings() as $packageMapping) {
			if (FALSE === in_array($packageMapping->getExtensionKey(), $foundExtensionKeys)) {
				$packageMappingContainer->removePackageMapping($packageMapping->getExtensionKey());
			}
		}

		$this->morphRepository->update($configuration);
	}

	private function convertExtensionKeyToPackageName($extensionKey) {
		return str_replace(' ', '.', ucwords(str_replace('_', ' ', $extensionKey)));
	}

	private function enrichPackageDataWithEmConfData(PackageMapping $packageMapping) {
		$emConfFile = $packageMapping->getFilePath() . '/ext_emconf.php';
		if (FALSE === file_exists($emConfFile)) {
			return;
		}

		$_EXTKEY = $packageMapping->getExtensionKey();
		/** @noinspection PhpIncludeInspection */
		include_once($emConfFile);
		if (isset($EM_CONF)) {
			$conf = $EM_CONF[$_EXTKEY];

			$packageMapping->setDescription($conf['description']);
			$packageMapping->setVersion($conf['version']);

			$trimExplode = function ($list) {
				return (new String($list))
					->split(',')
					->map(function (String $s) { return $s->strip(); })
					->filter(function (String $s) { return $s->length() > 0; })
					->map(function (String $s) { return $s->toPrimitive(); });
			};

			/** @var ArrayList $authors */
			$authors      = $trimExplode($conf['author']);
			$authorEmails = $trimExplode($conf['author_email']);

			for ($i = 0; $i < $authors->length(); $i++) {
				$packageMapping->addAuthor($authors[$i], isset($authorEmails[$i]) ? $authorEmails[$i] : NULL);
			}
		}
	}
}