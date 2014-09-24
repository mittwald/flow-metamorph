<?php
namespace Mw\Metamorph\Domain\Repository;



use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Persistence\RepositoryInterface;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Annotations as Flow;


/**
 * Class MorphConfigurationRepository
 *
 * @package Mw\Metamorph\Domain\Repository
 * @Flow\Scope("singleton")
 */
class MorphConfigurationRepository implements RepositoryInterface
{


    /**
     * @var \Mw\Metamorph\Domain\Factory\MorphConfigurationFactory
     * @Flow\Inject
     */
    protected $morphConfigurationFactory;


    /**
     * @var \TYPO3\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;


    /**
     * @var string
     */
    private $configurationPath;



    /**
     * Returns the object type this repository is managing.
     *
     * @return string
     * @api
     */
    public function getEntityClassName()
    {
        return 'Mw\\Metamorph\\Domain\\Model\\MorphConfiguration';
    }



    public function initializeObject()
    {
        $this->configurationPath = FLOW_PATH_ROOT . '/Build/Metamorph';

        if (!is_dir($this->configurationPath))
        {
            Files::createDirectoryRecursively($this->configurationPath);
        }
    }



    /**
     * Adds an object to this repository.
     *
     * @param object $object The object to add
     * @throws \BadMethodCallException
     * @return void
     * @api
     */
    public function add($object)
    {
        throw new \BadMethodCallException('Unsupported method.');
    }



    /**
     * Removes an object from this repository.
     *
     * @param object $object The object to remove
     * @throws \BadMethodCallException
     * @return void
     * @api
     */
    public function remove($object)
    {
        throw new \BadMethodCallException('Unsupported method.');
    }



    /**
     * Returns all objects of this repository.
     *
     * @return \Mw\Metamorph\Domain\Model\MorphConfiguration[] The query result
     * @api
     */
    public function findAll()
    {
        $packages = $this->getAllMorphPackages();

        /** @var \Mw\Metamorph\Domain\Factory\MorphConfigurationFactory $factory */
        $factory = $this->morphConfigurationFactory;

        return array_map(function (\TYPO3\Flow\Package\Package $package) use ($factory)
        {
            $filename      = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Morph.yml']);
            $name          = $package->getPackageKey();
            $configuration = Yaml::parse(file_get_contents($filename));

            return $factory->createFromConfigurationArray($name, $configuration);
        }, $packages);
    }



    /**
     * Finds an object matching the given identifier.
     *
     * @param mixed $identifier The identifier of the object to find
     * @return object The matching object if found, otherwise NULL
     * @api
     */
    public function findByIdentifier($identifier)
    {
        $package = $this->packageManager->getPackage($identifier);
        $filename      = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Morph.yml']);

        if (!file_exists($filename))
        {
            return NULL;
        }

        $configuration = Yaml::parse(file_get_contents($filename));
        return $this->morphConfigurationFactory->createFromConfigurationArray($identifier, $configuration);
    }



    /**
     * Returns a query for objects of this repository
     *
     * @return \TYPO3\Flow\Persistence\QueryInterface
     * @api
     */
    public function createQuery()
    {
        // TODO: Implement createQuery() method.
    }



    /**
     * Counts all objects of this repository
     *
     * @return integer
     * @api
     */
    public function countAll()
    {
        // TODO: Implement countAll() method.
    }



    /**
     * Removes all objects of this repository as if remove() was called for
     * all of them.
     *
     * @return void
     * @api
     */
    public function removeAll()
    {
        // TODO: Implement removeAll() method.
    }



    /**
     * Sets the property names to order results by. Expected like this:
     * array(
     *  'foo' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
     *  'bar' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $defaultOrderings The property names to order by by default
     * @return void
     * @api
     */
    public function setDefaultOrderings(array $defaultOrderings)
    {
        // TODO: Implement setDefaultOrderings() method.
    }



    /**
     * Schedules a modified object for persistence.
     *
     * @param object $object The modified object
     * @return void
     * @api
     */
    public function update($object)
    {
        // TODO: Implement update() method.
    }



    /**
     * Magic call method for repository methods.
     *
     * Provides three methods
     *  - findBy<PropertyName>($value, $caseSensitive = TRUE)
     *  - findOneBy<PropertyName>($value, $caseSensitive = TRUE)
     *  - countBy<PropertyName>($value, $caseSensitive = TRUE)
     *
     * @param string $method Name of the method
     * @param array $arguments The arguments
     * @return mixed The result of the repository method
     * @api
     */
    public function __call($method, $arguments)
    {
        // TODO: Implement __call() method.
    }



    private function getAllMorphPackages()
    {
        /** @var \TYPO3\Flow\Package\Package[] $packages */
        $packages      = $this->packageManager->getActivePackages();
        $foundPackages = [];

        foreach ($packages as $package)
        {
            if ($package->getPackageMetaData()->getPackageType() !== 'typo3-flow-site')
            {
                continue;
            }

            $morphPath = Files::concatenatePaths([$package->getConfigurationPath(), 'Metamorph', 'Morph.yml']);
            if (FALSE === file_exists($morphPath))
            {
                continue;
            }

            $foundPackages[] = $package;
        }

        return $foundPackages;
    }
}