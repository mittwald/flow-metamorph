<?php
namespace Mw\Metamorph\Persistence\Mapping\State;

use Helmich\EventBroker\Annotations as Event;
use Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent;
use Mw\Metamorph\Domain\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

trait YamlStorable {

	protected $workingDirectory = NULL;

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	protected function readYamlFile($filename, $ensureReviewed) {
		$data     = [];
		$filepath = $this->getWorkingFile($filename . '.yaml');

		if (file_exists($filepath)) {
			$content = file_get_contents($filepath);
			$data    = Yaml::parse($content);
		}

		if ($ensureReviewed && (!isset($data['reviewed']) || !$data['reviewed'])) {
			$what = strtolower(trim(preg_replace(',[A-Z],', ' $0', $filename)));
			throw new HumanInterventionRequiredException(
				'Please review and adjust the ' . $what . ' in <info>' . $filepath . '</info> and change the "reviewed" property to TRUE.',
				1399999104
			);
		}

		return $data;
	}

	protected function writeYamlFile($filename, array $data) {
		$filepath = $this->getWorkingFile($filename . '.yaml');
		$content  = Yaml::dump($data, 4, 2);

		file_put_contents($filepath, $content);

		return [];
	}

	protected function initializeWorkingDirectory($packageKey) {
		$package                = $this->packageManager->getPackage($packageKey);
		$this->workingDirectory = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Work']);
	}

	protected function getWorkingFile($filename) {
		return Files::concatenatePaths([$this->workingDirectory, $filename]);
	}

	protected function getArrayProperty(array $array, $key, $default = NULL) {
		return array_key_exists($key, $array) ? $array[$key] : $default;
	}

	protected function getSourceRelativePath($path, MorphConfiguration $morphConfiguration) {
		$prefix = $morphConfiguration->getSourceDirectory();
		return substr($path, strlen($prefix));
	}

	protected function getTargetRelativePath($path, $package) {
		$prefix = FLOW_PATH_ROOT . 'Packages/Application/' . $package . '/';
		return str_replace($prefix, '', $path);
		return substr($path, strlen($prefix));
	}

	/**
	 * @param MorphConfigurationFileModifiedEvent $event
	 * @Event\Event
	 */
	protected function publishConfigurationFileModifiedEvent(MorphConfigurationFileModifiedEvent $event) { }

}