<?php
namespace Mw\Metamorph\Logging\Aspect;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Logging\LoggingWrapper;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * @Flow\Aspect
 */
class RepositoryActionLoggingAspect
{



    /**
     * @var LoggingWrapper
     * @Flow\Inject
     */
    protected $loggingContainer;



    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\After("method(Mw\Metamorph\Domain\Repository\MorphConfigurationRepository->add())")
     */
    public function morphCreateAdvice(JoinPointInterface $joinPoint)
    {
        $configuration = $joinPoint->getMethodArgument('object');
        if ($configuration instanceof MorphConfiguration)
        {
            $this->loggingContainer->writeNested(
                'Added morph configuration <comment>%s</comment>.',
                [$configuration->getName()]
            );
        }
    }



    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\After("method(Mw\Metamorph\Domain\Repository\MorphConfigurationRepository->remove())")
     */
    public function morphRemoveAdvice(JoinPointInterface $joinPoint)
    {
        $configuration = $joinPoint->getMethodArgument('object');
        if ($configuration instanceof MorphConfiguration)
        {
            $this->loggingContainer->writeNested(
                'Removed morph configuration <comment>%s</comment>.',
                [$configuration->getName()]
            );
        }
    }


}