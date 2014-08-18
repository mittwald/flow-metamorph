<?php
namespace Mw\Metamorph\Domain\Model\State;


class ClassMapping implements \JsonSerializable
{



    const ACTION_MORPH = 'MORPH';


    const ACTION_IGNORE = 'IGNORE';


    private $sourceFile;


    private $oldClassName;


    private $newClassName;


    private $action = self::ACTION_MORPH;


    private $package;


    private $targetFile;



    public function __construct($sourceFile, $oldClassName, $newClassName, $package)
    {
        $this->newClassName = $newClassName;
        $this->oldClassName = $oldClassName;
        $this->package      = $package;
        $this->sourceFile   = $sourceFile;
    }



    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }



    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }



    /**
     * @param mixed $newClassName
     */
    public function setNewClassName($newClassName)
    {
        $this->newClassName = $newClassName;
    }



    /**
     * @return mixed
     */
    public function getNewClassName()
    {
        return $this->newClassName;
    }



    /**
     * @param mixed $oldClassName
     */
    public function setOldClassName($oldClassName)
    {
        $this->oldClassName = $oldClassName;
    }



    /**
     * @return mixed
     */
    public function getOldClassName()
    {
        return $this->oldClassName;
    }



    /**
     * @param mixed $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }



    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }



    /**
     * @param mixed $sourceFile
     */
    public function setSourceFile($sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }



    /**
     * @return mixed
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }



    /**
     * @param mixed $targetFile
     */
    public function setTargetFile($targetFile)
    {
        $this->targetFile = $targetFile;
    }



    /**
     * @return mixed
     */
    public function getTargetFile()
    {
        return $this->targetFile;
    }



    public function jsonSerialize()
    {
        $data = [
            'source'       => $this->sourceFile,
            'action'       => $this->action,
            'newClassname' => $this->newClassName,
            'package'      => $this->package
        ];

        if ($this->targetFile)
        {
            $data['target'] = $this->targetFile;
        }

        return $data;
    }



    static public function jsonUnserialize(array $data, $oldClassName)
    {
        $mapping         = new ClassMapping($data['source'], $oldClassName, $data['newClassname'], $data['package']);
        $mapping->action = $data['action'];

        if (isset($data['target']))
        {
            $mapping->targetFile = $data['target'];
        }

        return $mapping;
    }
}