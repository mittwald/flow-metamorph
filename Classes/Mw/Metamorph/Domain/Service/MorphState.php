<?php
namespace Mw\Metamorph\Domain\Service;


use Mw\Metamorph\Exception\HumanInterventionRequiredException;
use Symfony\Component\Yaml\Yaml;


class MorphState
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