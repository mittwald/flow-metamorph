<?php
namespace Mw\Metamorph\Domain\Model\State;

use Helmich\Scalars\Types\String;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 *
 * @Flow\Scope("prototype")
 */
class PackageMapping {

	const ACTION_MORPH = 'MORPH';

	const ACTION_IGNORE = 'IGNORE';

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $extensionKey;

	/**
	 * @var string
	 * @Flow\Validate(type="Mw\Metamorph\Domain\Validator\PackageKeyValidator")
	 */
	protected $packageKey;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $filePath;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $action = self::ACTION_MORPH;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var array
	 */
	protected $authors = [];

	public function __construct($filePath, $extensionKey = NULL) {
		$this->filePath     = $filePath;
		$this->extensionKey = $extensionKey ?: basename($filePath);
	}

	/**
	 * @return mixed
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	public function getExtensionKey() {
		return $this->extensionKey;
	}

	public function setPackageKey($packageKey) {
		$this->packageKey = $packageKey;
	}

	/**
	 * @return string
	 */
	public function getPackageKey() {
		return $this->packageKey;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @return mixed
	 */
	public function getVersion() {
		return $this->version;
	}

	public function addAuthor($name, $email = NULL) {
		$author = ['name' => $name];
		if (NULL !== $email) {
			$author['email'] = $email;
		}
		$this->authors[] = $author;
	}

	/**
	 * @return array
	 */
	public function getAuthors() {
		return $this->authors;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	public function isPackageKeyValid() {
		return (new String($this->packageKey))
			->split('.')
			->length() >= 2;
	}

}