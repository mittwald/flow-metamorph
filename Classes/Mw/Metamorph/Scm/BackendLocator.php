<?php
namespace Mw\Metamorph\Scm;


use Mw\Metamorph\Domain\Model\MorphConfiguration;
use Mw\Metamorph\Scm\Backend\GitBackend;
use Mw\Metamorph\Scm\Backend\NoOpBackend;
use Mw\Metamorph\Scm\Backend\ScmBackendInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Files;


/**
 * @Flow\Scope("singleton")
 */
class BackendLocator
{



    /**
     * @var PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;



    /**
     * @param string $backendIdentifier
     * @return ScmBackendInterface
     */
    public function getBackendByIdentifier($backendIdentifier)
    {
        switch (strtolower($backendIdentifier))
        {
            case 'git':
                return new GitBackend();
            default:
                return new NoOpBackend();
        }
    }



    /**
     * @param MorphConfiguration $configuration
     * @return ScmBackendInterface
     */
    public function getBackendByConfiguration(MorphConfiguration $configuration)
    {
        $package = $this->packageManager->getPackage($configuration->getName());
        if (file_exists(Files::concatenatePaths([$package->getPackagePath(), '.git'])))
        {
            return new GitBackend();
        }

        return new NoOpBackend();
    }

}