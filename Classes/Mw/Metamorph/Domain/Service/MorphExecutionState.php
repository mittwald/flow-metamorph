<?php
namespace Mw\Metamorph\Domain\Service;


use Mw\Metamorph\Domain\Model\State\ClassMappingContainer;
use Mw\Metamorph\Domain\Model\State\PackageMapping;
use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Symfony\Component\Yaml\Yaml;


class MorphExecutionState
{



    private $workingDirectory;



    public function __construct($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }



    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }



    public function readYamlFile($filename, $ensureReviewed)
    {
        $data     = [];
        $filepath = $this->getWorkingFile($filename . '.yaml');

        if (file_exists($filepath))
        {
            $content = file_get_contents($filepath);
            $data    = Yaml::parse($content);
        }

        if ($ensureReviewed && (!isset($data['reviewed']) || !$data['reviewed']))
        {
            $what = strtolower(trim(preg_replace(',[A-Z],', ' $0', $filename)));
            throw new HumanInterventionRequiredException(
                'Please review and adjust the ' . $what . ' in <i>' . $filepath . '</i> and change the "reviewed" property to TRUE.',
                1399999104
            );
        }

        return $data;
    }



    /**
     * @param bool $ensureReviewed
     * @return PackageMapping[]
     */
    public function getPackageMapping($ensureReviewed = TRUE)
    {
        $extensionData = $this->readYamlFile('PackageMap', $ensureReviewed)['extensions'];
        return array_map(
            function ($key) use ($extensionData)
            {
                return PackageMapping::jsonUnserialize(
                    $extensionData[$key],
                    $key
                );
            },
            array_keys($extensionData)
        );
    }



    /**
     * @param bool $ensureReviewed
     * @return ClassMappingContainer
     */
    public function getClassMapping($ensureReviewed = TRUE)
    {
        $classData = $this->readYamlFile('ClassMap', $ensureReviewed);
        return ClassMappingContainer::jsonUnserialize($classData);
    }



    /**
     * @param ClassMappingContainer $classMappingContainer
     */
    public function updateClassMapping(ClassMappingContainer $classMappingContainer)
    {
        $this->writeYamlFile('ClassMap', $classMappingContainer->jsonSerialize());
    }



    public function getWorkingFile($filename)
    {
        return $this->workingDirectory . DIRECTORY_SEPARATOR . $filename;
    }



    public function writeYamlFile($filename, array $data)
    {
        $filepath = $this->getWorkingFile($filename . '.yaml');
        $content  = Yaml::dump($data, 4, 2);

        file_put_contents($filepath, $content);

        return [];
    }


}