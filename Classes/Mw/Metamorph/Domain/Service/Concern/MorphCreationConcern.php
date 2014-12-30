<?php
namespace Mw\Metamorph\Domain\Service\Concern;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\Extension\PatternExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\UnionMatcher;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\MetaData;

/**
 * Class MorphCreationConcern
 */
class MorphCreationConcern {

	/**
	 * @var MorphConfigurationRepository
	 * @Flow\Inject
	 */
	protected $morphRepository;

	public function create($packageKey, MorphCreationDto $data, OutputInterface $out) {
		$extensionMatchers = [];
		foreach ($data->getExtensionPatterns() as $pattern) {
			$extensionMatchers[] = new PatternExtensionMatcher($pattern);
		}

		$morphConfiguration = new MorphConfiguration($packageKey, $data->getSourceDirectory());
		$morphConfiguration->setTableStructureMode($data->getTableStructureMode());
		$morphConfiguration->setPibaseRefactoringMode($data->getPibaseRefactoringMode());

		if (count($extensionMatchers)) {
			$morphConfiguration->setExtensionMatcher(new UnionMatcher($extensionMatchers));
		}

		$this->morphRepository->add($morphConfiguration);
		return $morphConfiguration;
	}

}