<?php
namespace Mw\Metamorph\Step\DatabaseMigration\Tca;

use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Parser\PHP\PhpParser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Annotations as Flow;

class TcaLoader {

	/**
	 * @var PhpParser
	 * @Flow\Inject
	 */
	protected $parser;

	public function loadTcaForPackage(PackageMapping $packageMapping, Tca $tca) {
		$extTables = Files::concatenatePaths([$packageMapping->getFilePath(), 'ext_tables.php']);
		if (!file_exists($extTables)) {
			return;
		}

		$stmts   = $this->parser->parseFile($extTables);

		$traverser = new NodeTraverser();
		$traverser->addVisitor(new NameResolver());
		$traverser->addVisitor(new TcaLoaderVisitor($tca, $packageMapping, $extTables));

		$traverser->traverse($stmts);
	}

}