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

```
composer create mittwald-typo3/flow-distribution-metamorph
```

Alternatively, you can require it using composer:

```
{
    "require": {
        "mittwald-typo3/flow-metamorph": "*"
    }
}
```
