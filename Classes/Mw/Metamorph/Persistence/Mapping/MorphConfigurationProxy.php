<?php
namespace Mw\Metamorph\Persistence\Mapping;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Model\Extension\AllMatcher;
use Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\PatternExtensionMatcher;
use Mw\Metamorph\Domain\Model\Extension\UnionMatcher;
use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Exception\InvalidConfigurationException;
use Mw\Metamorph\Persistence\Mapping\State\ClassMappingContainerProxy;
use Mw\Metamorph\Persistence\Mapping\State\PackageMappingContainerProxy;
use TYPO3\Flow\Package\PackageInterface;


class MorphConfigurationProxy extends MorphConfiguration
{



    public function __construct($name, array $data, PackageInterface $package)
    {
        $this->name             = $name;
        $this->sourceDirectory  = $this->getPropertyFromData($data, 'sourceDirectory');
        $this->package          = $package;
        $this->extensionMatcher = $this->buildExtensionMatcher($data);

        $this->tableStructureMode = $this->getPropertyFromData(
            $data,
            'tableStructureMode',
            FALSE,
            self::TABLE_STRUCTURE_KEEP
        );

        $this->pibaseRefactoringMode = $this->getPropertyFromData(
            $data,
            'pibaseRefactoringMode',
            FALSE,
            self::PIBASE_REFACTOR_CONSERVATIVE
        );

        $this->packageMappingContainer = new PackageMappingContainerProxy($this);
        $this->classMappingContainer   = new ClassMappingContainerProxy($this);
    }



    /**
     * @param array  $data
     * @param string $key
     * @param bool   $required
     * @param mixed  $default
     * @return null
     * @throws InvalidConfigurationException
     */
    private function getPropertyFromData($data, $key, $required = TRUE, $default = NULL)
    {
        if (array_key_exists($key, $data))
        {
            return $data[$key];
        }
        else
        {
            if ($required)
            {
                throw new InvalidConfigurationException(
                    'Expected property "' . $key . '" in morph configuration!',
                    1411832225
                );
            }
            return $default;
        }
    }



    /**
     * @param array $data
     * @return ExtensionMatcher
     * @throws InvalidConfigurationException
     */
    private function buildExtensionMatcher(array $data)
    {
        $rootExtensionMatcher = new AllMatcher();
        $extensionMatchers    = [];

        foreach ((array)$data['extensions'] as $extensionConfiguration)
        {
            if (array_key_exists('pattern', $extensionConfiguration))
            {
                $extensionMatchers[] = new PatternExtensionMatcher($extensionConfiguration['pattern']);
            }
            else
            {
                throw new InvalidConfigurationException(
                    'Invalid extension matcher configuration: ' . json_encode($extensionConfiguration),
                    1399996413
                );
            }
        }

        if (count($extensionMatchers) > 0)
        {
            $rootExtensionMatcher = new UnionMatcher($extensionMatchers);
            return $rootExtensionMatcher;
        }
        return $rootExtensionMatcher;
    }


}