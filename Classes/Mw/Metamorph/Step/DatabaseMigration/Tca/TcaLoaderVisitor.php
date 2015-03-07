<?php
namespace Mw\Metamorph\Step\DatabaseMigration\Tca;

use Helmich\PhpEvaluator\Evaluator\ConstantStore;
use Helmich\PhpEvaluator\Evaluator\Evaluator;
use Helmich\PhpEvaluator\Evaluator\FunctionStore;
use Helmich\PhpEvaluator\Evaluator\VariableStore;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use TYPO3\Flow\Annotations as Flow;

class TcaLoaderVisitor extends NodeVisitorAbstract {

	private $tca;

	/**
	 * @var PackageMapping
	 */
	private $packageMapping;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var string
	 */
	private $currentFile = NULL;

	/**
	 * @var Evaluator
	 * @Flow\Inject
	 */
	protected $evaluator;

	public function __construct(Tca $tca, PackageMapping $packageMapping, $currentFile = NULL) {
		$this->tca            = $tca;
		$this->packageMapping = $packageMapping;
		$this->currentFile    = $currentFile;
		$this->parser         = new Parser(new Lexer());
	}

	public function initializeObject() {
		$extPathFunc = function ($extKey) {
			return $this->packageMapping->getFilePath() . '/';
		};

		$functions = new FunctionStore();
		$functions->setFallbackFunction(function ($name, $arguments) { });

		$functions['TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath'] = $extPathFunc;
		$functions['t3lib_extMgm::extPath']                                      = $extPathFunc;

		$variables            = new VariableStore(
			[
				'_EXTKEY' => $this->packageMapping->getExtensionKey(),
				'TCA'     => $this->tca
			]
		);
		$variables['GLOBALS'] = $variables;
		$variables->setLookupFunction(function($name) {
			return NULL;
		});

		$this->evaluator->setGlobalScope($variables);
		$this->evaluator->setConstantStore(new ConstantStore());
		$this->evaluator->setFunctionStore($functions);
	}

	public function enterNode(Node $node) {
		if ($this->isTcaAssignment($node)) {
			/** @var Node\Expr\Assign $node */
			$val = $this->evaluator->evaluateExpression($node->expr);
			$var = $node->var;

			$target =& $this->tca;

			if ($var instanceof Node\Expr\ArrayDimFetch) {
				list($var, $dimensions) = $this->getVarAndKeysFromArrayFetch($var);
				foreach ($dimensions as $dimension) {
					$target =& $target[$dimension];
				}
			}

			$target = $val;

			if (isset($val['ctrl']['dynamicConfigFile']) && $val['ctrl']['dynamicConfigFile'] !== $this->currentFile) {
				$tcaFile = $val['ctrl']['dynamicConfigFile'];
				$content = file_get_contents($tcaFile);
				$stmts   = $this->parser->parse($content);

				$traverser = new NodeTraverser();
				$traverser->addVisitor(new NameResolver());
				$traverser->addVisitor(new TcaLoaderVisitor($this->tca, $this->packageMapping, $tcaFile));

				$traverser->traverse($stmts);
			}
		}
	}

	private function isTcaAssignment(Node $node) {
		if (!$node instanceof Node\Expr\Assign) {
			return FALSE;
		}

		$left = $node->var;
		if ($left instanceof Node\Expr\Variable && $left->name === 'TCA') {
			return TRUE;
		}

		if ($left instanceof Node\Expr\ArrayDimFetch) {
			list($var, $_) = $this->getVarAndKeysFromArrayFetch($left);

			/** @var Node\Expr\Variable $var */
			if ($var->name === 'TCA') {
				return TRUE;
			}
		}

		return FALSE;
	}

	private function getVarAndKeysFromArrayFetch(Node\Expr\ArrayDimFetch $node) {
		$left = $node;
		$keys = [];

		while (!$left instanceof Node\Expr\Variable) {
			if ($left->dim !== NULL) {
				$keys[] = $this->evaluator->evaluateExpression($left->dim);
			} else {
				$keys[] = NULL;
			}
			$left = $left->var;
		}

		return [$left, $keys];
	}

}