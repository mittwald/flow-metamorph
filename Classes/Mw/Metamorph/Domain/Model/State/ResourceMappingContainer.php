<?php
namespace Mw\Metamorph\Domain\Model\State;

use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Domain\Model\State
 *
 * @Flow\Scope("prototype")
 */
class ResourceMappingContainer implements Reviewable {

	use ReviewableTrait;

	/**
	 * @var array<\Mw\Metamorph\Domain\Model\State\ResourceMapping>
	 */
	protected $resourceMappings = [];

	public function hasResourceMapping($sourceFile) {
		return NULL !== $this->getResourceMapping($sourceFile);
	}

	public function getResourceMapping($sourceFile) {
		foreach ($this->resourceMappings as $resourceMapping) {
			if ($sourceFile === $resourceMapping->getSourceFile()) {
				return $resourceMapping;
			}
		}
		return NULL;
	}

	/**
	 * @return ResourceMapping[]
	 */
	public function getResourceMappings() {
		return $this->resourceMappings;
	}

	public function addResourceMapping(ResourceMapping $resourceMapping) {
		if (FALSE === $this->hasResourceMapping($resourceMapping->getSourceFile())) {
			$this->reviewed           = FALSE;
			$this->resourceMappings[] = $resourceMapping;
		}
	}
} 