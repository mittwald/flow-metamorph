Metamorph - TYPO3 CMS to TYPO3 Flow converter
=============================================

Author
------

Martin Helmich  
Mittwald CM Service GmbH & Co. KG

Synopsis
--------

Metamorph is a tool intended for converting TYPO3 sites into TYPO3 Flow
applications and (later) TYPO3 Neos sites.

Installation
------------

Metamorph is a TYPO3 Flow package. Currently, the best way to install it is to
use the [distribution package](https://github.com/mittwald/flow-distribution-metamorph),
using [Composer](http://getcomposer.org).
Currently, Metamorph depends on a custom version of TYPO3 Flow which
contains some not-yet merged bugfixes and features:

    composer create mittwald-typo3/flow-distribution-metamorph

Alternatively, you can require it using composer:

    {
        "require": {
            "mittwald-typo3/flow-metamorph": "*"
        }
    }

Features (current)
------------------

- Convert Extbase extensions into TYPO3 Flow packages. This includes:
    
    - Rewriting non-namespaced classes to namespaced classes
    - Replace Extbase classes with their TYPO3 Flow counterparts, where possible
      and provide compatibility classes otherwise
    - Replace Extbase annotations (like `@inject`) the respective Flow annotations
      (like `@Flow\Inject`), including namespace import.
    - Enrich Extbase domain models with annotations for the Doctrine2 ORM
      framework.
    - Translate locallang XML files to XLIFF.
    - Create Doctrine migrations for converted entity classes.

Features (planned)
------------------

- More features for Extbase migration:

    - Option to keep the existing database structure
    - *(add custom wishes here)*

- Convert pibase extensions into TYPO3 Flow packages. This includes:

    - Rewriting non-namespaced classes to namespaced classes
    - Rewrite classes to use TYPO3 Flow APIs where possible, and provice
      compatibility classes otherwise
    - Generate controller classes wrapping the migrated plugin classes

- Convert plugins (both Extbase and piBase) into TYPO3 Neos plugins.

- Convert TYPO3's page tree into the TYPO3 Neos content repository format