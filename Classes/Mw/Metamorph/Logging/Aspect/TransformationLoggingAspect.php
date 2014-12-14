<?php
namespace Mw\Metamorph\Logging\Aspect;


use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Mw\Metamorph\Logging\LoggingWrapper;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * Class TransformationLoggingAspect
 *
 * @package    Mw\Metamorph
 * @subpackage Logging\Aspect
 * @Flow\Aspect
 */
class TransformationLoggingAspect
{



    /**
     * @var LoggingWrapper
     * @Flow\Inject
     */
    protected $loggingWrapper;



    /**
     * @param JoinPointInterface $joinPoint
     * @return mixed
     *
     * @Flow\Around("within(Mw\Metamorph\Domain\Service\MorphServiceInterface) && method(.*->execute())")
     */
    public function morphExecutionAdvice(JoinPointInterface $joinPoint)
    {
        /** @var MorphConfiguration $configuration */
        $configuration = $joinPoint->getMethodArgument('configuration');

        $this->loggingWrapper->writeNested('Executing morph <comment>%s</comment>.', [$configuration->getName()]);
        $this->loggingWrapper->incrementNestingLevel();

        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);

        $this->loggingWrapper->decrementNestingLevel();
        $this->loggingWrapper->writeNested('Done.');

        return $result;
    }



    /**
     * @param JoinPointInterface $joinPoint
     * @return mixed
     *
     * @Flow\Around("within(Mw\Metamorph\Transformation\Transformation) && method(.*->execute())")
     */
    public function transformationExecutionAdvice(JoinPointInterface $joinPoint)
    {
        $this->loggingWrapper->writeNested('Executing step <comment>%s</comment>.', [$joinPoint->getClassName()]);
        $this->loggingWrapper->incrementNestingLevel();

        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);

        $this->loggingWrapper->decrementNestingLevel();
        return $result;
    }



    /**
     * @param JoinPointInterface $joinPoint
     * @throws HumanInterventionRequiredException
     * @throws \Exception
     * @return mixed
     *
     * @Flow\Around("within(Mw\Metamorph\Domain\Service\MorphServiceInterface) && method(.*->execute())")
     */
    public function humanInterventionLoggingAdvice(JoinPointInterface $joinPoint)
    {
        try
        {
            $result = $joinPoint->getAdviceChain()->proceed($joinPoint);
            return $result;
        }
        catch (HumanInterventionRequiredException $exception)
        {
            $out = $this->loggingWrapper;

            $out->writeln('');
            $out->writeln('<question>  Human intervention required  </question>');
            $out->writeln('');

            $i = str_repeat(" ", 2);
            (new String($exception->getMessage()))
                ->split("\n")
                ->map(function($l) use ($i, $out) { $out->writeFormatted($l, 2); });

            $out->writeln('');

            throw $exception;
        }
    }



    private function indent($text, $indent=2)
    {
        $i = str_repeat(" ", $indent);
        return (new String($text))
            ->split("\n")
            ->map(function($l) use ($i) { var_dump($i . $l); return $i . $l; })
            ->join("\n");
    }



    /**
     * @param JoinPointInterface $joinPoint
     * @return mixed
     *
     * @Flow\AfterReturning("within(Mw\Metamorph\Transformation\Transformation) && method(.*->log())")
     */
    public function transformationLoggingAdvice(JoinPointInterface $joinPoint)
    {
        list($message, $arguments) = array_values($joinPoint->getMethodArguments());
        $this->loggingWrapper->writeNested($message, $arguments);
    }

}