<?php
namespace Mw\Metamorph\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\Dto\MorphCreationDto;
use Symfony\Component\Console\Output\OutputInterface;

interface MorphServiceInterface {

	public function reset(MorphConfiguration $configuration, OutputInterface $out);

	/**
	 * Creates a new morph package.
	 *
	 * @param string           $packageKey The package key to use.
	 * @param MorphCreationDto $data       Data necessary for package creation.
	 * @param OutputInterface  $out        Output stream.
	 * @return MorphConfiguration The created morph configuration.
	 */
	public function create($packageKey, MorphCreationDto $data, OutputInterface $out);

	public function execute(MorphConfiguration $configuration, OutputInterface $out);

}