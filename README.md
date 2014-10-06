Metamorph - TYPO3 CMS to TYPO3 Flow converter
=============================================

**CAUTION**: This package is currently unter heavy development and by no means
stable! APIs and behaviour can change at any point without notice.

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

Getting started
---------------

Metamorph manages migration configuration for single TYPO3 CMS sites in the form
of *morphs*. Each morph is a TYPO3 Flow package, with the migration configuration
stored in `Configuration/Metamorph/Morph.yml`.

You can create a new morph configuration by using the `morph:create` command:

    ./flow morph:create Mw.ExampleSite

The first parameter will be used as package key. The command will then prompt you
for a set of configuration options.

The `morph:create` command will then generate a morph configuration file that
will look like this:

    sourceDirectory: /var/www/extbasefluid-examplesite/html
    extensions:
        - pattern: /^mittwald_/
        - pattern: /^helmich_/
    tableStructureMode: MIGRATE
    pibaseRefactoringMode: CONSERVATIVE
    versionControlSystem: GIT

You can then execute the morph configuration using the `morph:execute` command:

    ./flow morph:execute

This command will run in several steps, prompting the user to review automatically
generated files:

1. In the first step, the *package map* will be created. During this step, metamorph
   will find all TYPO3 extensions that match one of the regular expression matchers
   defined in the morph configuration. It will write a file `Configuration/Metamorph/Work/PackageMap.yml`
   into your morph package. In this file, you will find a mapping from TYPO3 extension
   names to TYPO3 Flow package names.

   Metamorph will try to auto-derive the package names from the extension keys. You
   can adjust the auto-generated package names as you see fit and then change the
   `reviewed` property in this file to `true` and re-run `morph:execute` again.

   If extensions are added later-on to the original TYPO3 CMS site, these will be added
   to the package map.

2. In the second step, metamorph will scan the configured extensions for PHP classes
   and generate a *class map* from all found classes. It will write a file
   `Configuration/Metamorph/Work/ClassMap.yml`, in which all classes and their new
   class names will be stored.
   
   Again, the target class names will be auto-derived by metamorph and you can change
   them as you like. Then set the `reviewed` property and re-run `morph:execute`.

3. In the third step, Metamorph will scan for resource files in your extensions. If
   you have a `Resources/` directory, this will simply be copied into your TYPO3 Flow
   package. The resource mapping will be written into the file
   `Configuration/Metamorph/Work/ResourceMap.yml`. Simply proceed as like before.

4. After that, Metamorph will start with actually migrating the selected extensions.
   The TYPO3 Flow packages will be created, classes and resources are copied and
   automatically refactored as necessary. If errors occur along the way, you can
   simply re-run `morph:execute` as many times as you like after you have fixed them.