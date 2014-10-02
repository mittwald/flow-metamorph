<?php
namespace Mw\Metamorph\Transformation\Helper\Namespaces;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class ImportHelper
{



    public function importNamespaceIntoOtherNamespace(Namespace_ $namespaceNode, $importNamespace, $alias = NULL)
    {
        $foundImports    = [];
        $found           = FALSE;
        $lastImportIndex = NULL;

        $fqnn  = new FullyQualified($importNamespace);
        $alias = $alias ?: $fqnn->getLast();

        foreach ($namespaceNode->stmts as $key => $stmt)
        {
            if (!$stmt instanceof Use_)
            {
                continue;
            }

            $lastImportIndex = $key;
            foreach ($stmt->uses as $use)
            {
                $foundImports[] = [
                    'name'  => $use->name->toString(),
                    'alias' => $use->alias
                ];

                var_dump($use->alias, $use->name->toString(), $importNamespace);

                if ($alias == $use->alias && $use->name->toString() == $importNamespace)
                {
                    $found = TRUE;
                }
            }
        }

        if ($found)
        {
            return $namespaceNode;
        }

        $innerUse = new UseUse(new FullyQualified($importNamespace), $alias);
        $outerUse = new Use_([$innerUse]);

        $stmts = $namespaceNode->stmts;

        array_splice($stmts, $lastImportIndex + 1, 0, [$outerUse]);

        $namespaceNode->stmts = $stmts;
        return $namespaceNode;
    }



}