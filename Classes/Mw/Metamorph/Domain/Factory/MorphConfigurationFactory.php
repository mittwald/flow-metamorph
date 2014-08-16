<?php
namespace Mw\Metamorph\Domain\Factory;


use Mw\Metamorph\Domain\Model\Extension\AllMatcher;
use Mw\Metamorph\Domain\Model\Extension\PatternExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\UnionMatcher;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Exception\InvalidConfigurationException;
use TYPO3\Flow\Annotations as Flow;


/**
 * Class MorphConfigurationFactory
 *
 * @package Mw\Metamorph\Domain\Factory
 * @Flow\Scope("singleton")
 */
class MorphConfigurationFactory
{


    /**
     * @param string $name
     * @param array  $morphConfiguration
     * @return \Mw\Metamorph\Domain\Model\MorphConfiguration
     */
    public function createFromConfigurationArray($name, array $morphConfiguration)
    {
        $this->assertConfigurationKeysExist(
            $morphConfiguration,
            ['sourceDirectory', 'extensions']
        );

        $extensionMatcher = $this->buildExtensionMatcher($morphConfiguration);

        $morph = new MorphConfiguration(
            $name,
            $morphConfiguration['sourceDirectory'],
            $extensionMatcher
        );

        return $morph;
    }



    private function assertConfigurationKeysExist(array $configuration, array $keys)
    {
        foreach ($keys as $key)
        {
            if (!array_key_exists($key, $configuration))
            {
                throw new InvalidConfigurationException('Missing attribute "' . $key . '" in configuration.');
            }
        }
    }



    /**
     * @param array $configuration
     * @throws \Mw\Metamorph\Exception\InvalidConfigurationException
     * @return \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher[]
     */
    private function buildExtensionMatcher(array $configuration)
    {
        $matchers = [];
        foreach ($configuration['extensions'] as $matcherConfig)
        {
            if (isset($matcherConfig['pattern']))
            {
                $matchers[] = new PatternExtensionMatcher($matcherConfig['pattern']);
                continue;
            }

            throw new InvalidConfigurationException(
                'Invalid extension matcher configuration: ' . json_encode($matcherConfig),
                1399996413
            );
        }

        if (count($matchers) > 0)
        {
            return new UnionMatcher($matchers);
        }
        return new AllMatcher();
    }
} 