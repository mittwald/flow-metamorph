<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Tca;


use Mw\Metamorph\Domain\Model\State\PackageMapping;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use TYPO3\Flow\Utility\Files;

class TcaLoader
{



    /**
     * @var Parser
     */
    private $parser;



    public function __construct()
    {
        $this->parser = new Parser(new Lexer());
    }



    public function loadTcaForPackage(PackageMapping $packageMapping, Tca $tca)
    {
        $extTables = Files::concatenatePaths([$packageMapping->getFilePath(), 'ext_tables.php']);
        if (!file_exists($extTables))
        {
            return;
        }

        $content = file_get_contents($extTables);
        $stmts   = $this->parser->parse($content);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new TcaLoaderVisitor($tca, $packageMapping, $extTables));

        $traverser->traverse($stmts);
    }

}