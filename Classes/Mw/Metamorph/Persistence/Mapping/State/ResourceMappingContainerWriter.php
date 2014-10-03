<?php
namespace Mw\Metamorph\Persistence\Mapping\State;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */


use Mw\Metamorph\Domain\Event\MorphConfigurationFileModifiedEvent;
use Mw\Metamorph\Domain\Model\MorphConfiguration;


class ResourceMappingContainerWriter
{



    use YamlStorable;



    public function writeMorphResourceMapping(MorphConfiguration $morphConfiguration)
    {
        $this->initializeWorkingDirectory($morphConfiguration->getName());

        $classMappings = $morphConfiguration->getResourceMappingContainer();
        $data          = ['reviewed' => $classMappings->isReviewed(), 'resources' => []];

        foreach ($classMappings->getResourceMappings() as $resourceMapping)
        {
            $mapped = [
                'target'  => $resourceMapping->getTargetFile(),
                'package' => $resourceMapping->getPackage()
            ];

            if ($resourceMapping->getTargetFile())
            {
                $mapped['target'] = $resourceMapping->getTargetFile();
            }

            $data['resources'][$resourceMapping->getSourceFile()] = $mapped;
        }

        if (count($classMappings->getResourceMappings()))
        {
            $this->writeYamlFile('ResourceMap', $data);
            $this->publishConfigurationFileModifiedEvent(
                new MorphConfigurationFileModifiedEvent(
                    $morphConfiguration,
                    $this->getWorkingFile('ResourceMap.yaml'),
                    'Updated resource map.'
                )
            );
        }
    }



}