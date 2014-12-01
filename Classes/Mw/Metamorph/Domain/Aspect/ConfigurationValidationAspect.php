<?php
namespace Mw\Metamorph\Domain\Aspect;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Domain\Service\MorphValidationService;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\View\ValidationResultRenderer;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * Aspect that handles the validation of morph configurations before each
 * transformation step.
 *
 * @package    Mw\Metamorph
 * @subpackage Domain\Aspect
 *
 * @Flow\Aspect
 */
class ConfigurationValidationAspect
{



    /**
     * @var MorphValidationService
     * @Flow\Inject
     */
    protected $morphValidationService;


    /**
     * @var ValidationResultRenderer
     * @Flow\Inject
     */
    protected $validationResultRenderer;



    /**
     * @Flow\Pointcut("within(Mw\Metamorph\Transformation\Transformation) && !classAnnotatedWith(Mw\Metamorph\Annotations\SkipConfigurationValidation) && method(.*->execute())")
     */
    public function transformationExecutionPointcut() { }



    /**
     * @param JoinPointInterface $joinPoint
     * @throws HumanInterventionRequiredException
     *
     * @Flow\Before("Mw\Metamorph\Domain\Aspect\ConfigurationValidationAspect->transformationExecutionPointcut")
     */
    public function validateMorphConfigurationBeforeTransformationExecution(JoinPointInterface $joinPoint)
    {
        $configuration = array_values($joinPoint->getMethodArguments())[0];
        if ($configuration instanceof MorphConfiguration)
        {
            $validationResults = $this->morphValidationService->validate($configuration);
            if (NULL !== $validationResults && $validationResults->hasErrors())
            {
                $msgTemplate = <<<EOF
The automatically generated morph configuration is invalid. Please fix the validation errors below manually.
Have a look at the files in the <comment>%s</comment> directory.
EOF;

                $msg = sprintf($msgTemplate, $configuration->getPackage()->getClassesPath() . 'Metamorph');

                throw new HumanInterventionRequiredException(
                    $this->validationResultRenderer->renderValidationResult($validationResults, $msg)
                );
            }
        }
    }

}