<?php
namespace Mw\Metamorph\Annotations;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


/**
 * Annotations that marks Transformation for which the morph configuration
 * should not be validated.
 *
 * Some transformations are actually used for *creating* the morph configuration
 * any may create yet-invalid morph configuration. In these steps, the morph
 * configuration should not be validated, because invalid configurations are
 * actually allowed.
 *
 * @package    Mw\Metamorph
 * @subpackage Annotations
 *
 * @Annotation
 * @Target("CLASS")
 */
class SkipConfigurationValidation
{



}