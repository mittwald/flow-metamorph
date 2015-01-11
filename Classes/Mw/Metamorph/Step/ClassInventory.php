<?php
namespace Mw\Metamorph\Step;

use Mw\Metamorph\Annotations as Metamorph;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Model\State\ClassMapping;
use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Domain\Repository\MorphConfigurationRepository;
use Mw\Metamorph\Domain\Service\MorphExecutionState;
use Mw\Metamorph\Parser\ParserInterface;
use Mw\Metamorph\Parser\PHP\EarlyStoppingTraverser;
use Mw\Metamorph\Parser\PHP\SkipTraversalException;
use Mw\Metamorph\Step\ClassNameConversion\ClassNameConversionStrategy;
use Mw\Metamorph\Transformation\AbstractTransformation;
use Mw\Metamorph\Transformation\Helper\ClosureVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use TYPO3\Flow\Annotations as Flow;

/**
 * @package    Mw\Metamorph
 * @subpackage Transformation
 *
 * @Metamorph\SkipClassReview
 * @Metamorph\SkipResourceReview
 */
class ClassInventory extends AbstractTransformation {

	/** @var ClassMappingContainer */
	private $classMappingContainer = NULL;

	/**
	 * @var ParserInterface
	 * @Flow\Inject
	 */
	protected $parser;

	/**
	 * @var MorphConfigurationRepository
	 * @Flow\Inject
	 */
	protected $morphRepository;

	/**
	 * @var ClassNameConversionStrategy
	 * @Flow\Inject
	 */
	protected $classNameConversionStrategy;

	public function execute(MorphConfiguration $configuration, MorphExecutionState $state) {
		$this->classMappingContainer = $configuration->getClassMappingContainer();

		foreach ($configuration->getPackageMappingContainer()->getPackageMappings() as $packageMapping) {
			if ($packageMapping->getAction() === PackageMapping::ACTION_MORPH) {
				$this->readClassesFromExtension($packageMapping);
			}
		}

		$this->morphRepository->update($configuration);
	}

	private function readClassesFromExtension(PackageMapping $packageMapping) {
		$directoryIterator = new \RecursiveDirectoryIterator($packageMapping->getFilePath());
		$iteratorIterator  = new \RecursiveIteratorIterator($directoryIterator);
		$regexIterator     = new \RegexIterator($iteratorIterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

		$classList = new \ArrayObject();

		foreach ($regexIterator as $match) {
			$filename = $match[0];
			$this->readClassesFromFile($filename, $classList);
		}

		$this->log(
			'<comment>%d</comment> classes found in EXT:<comment>%s</comment>.',
			[count($classList), $packageMapping->getExtensionKey()]
		);

		foreach ($classList as $className => $filename) {
			if (FALSE === $this->classMappingContainer->hasClassMapping($className)) {
				$classMapping = new ClassMapping(
					$filename, $className, $this->guessMorphedClassName(
						$className,
						$filename,
						$packageMapping
					), $packageMapping->getPackageKey()
				);

				$this->classMappingContainer->addClassMapping($classMapping);
			}
		}
	}

	private function readClassesFromFile($filename, \ArrayAccess $classList) {
		$syntaxTree  = $this->parser->parseFile($filename);

		$traverser = new EarlyStoppingTraverser();
		$traverser->addVisitor(new NameResolver());
		$traverser->addVisitor($this->buildClassFinderVisitor($filename, $classList));

		foreach ($this->settings['visitors'] as $visitorClassName) {
			if (FALSE === class_exists($visitorClassName)) {
				$visitorClassName = 'Mw\\Metamorph\\Step\\ClassInventory\\' . $visitorClassName;
			}

			$visitor = new $visitorClassName();
			if ($visitor instanceof NodeVisitor) {
				$traverser->addVisitor($visitor);
			}
		}

		$traverser->traverse($syntaxTree);
	}

	private function guessMorphedClassName($className, $filename, PackageMapping $packageMapping) {
		$newPackageNamespace       = str_replace('.', '\\', $packageMapping->getPackageKey());
		return $this->classNameConversionStrategy->convertClassName(
			$newPackageNamespace,
			$className,
			$filename,
			$packageMapping->getExtensionKey()
		);
	}

	/**
	 * @param              $filename
	 * @param \ArrayAccess $classList
	 * @return ClosureVisitor
	 */
	private function buildClassFinderVisitor($filename, \ArrayAccess $classList) {
		$visitor = new ClosureVisitor();
		$visitor->setOnEnter(function (Node $node) use ($filename, $classList) {
			if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_) {
				$name             = $node->namespacedName->toString();
				$classList[$name] = $filename;

				throw new SkipTraversalException();
			}
		});
		return $visitor;
	}
}
