<?php
namespace Mw\Metamorph\Domain\Model\State;


class PackageMapping
{



    const ACTION_MORPH = 'MORPH';


    const ACTION_IGNORE = 'IGNORE';


    protected $extensionKey;


    protected $packageKey;


    protected $filePath;


    protected $action = self::ACTION_MORPH;


    protected $description;


    protected $version;


    protected $authors = [];



    public function __construct($filePath, $extensionKey = NULL)
    {
        $this->filePath     = $filePath;
        $this->extensionKey = $extensionKey ?: basename($filePath);
    }



    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }



    public function getExtensionKey()
    {
        return $this->extensionKey;
    }



    public function setPackageKey($packageKey)
    {
        $this->packageKey = $packageKey;
    }



    /**
     * @return string
     */
    public function getPackageKey()
    {
        return $this->packageKey;
    }



    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }



    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }



    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }



    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }



    public function addAuthor($name, $email = NULL)
    {
        $author = ['name' => $name];
        if (NULL !== $email)
        {
            $author['email'] = $email;
        }
        $this->authors[] = $author;
    }



    /**
     * @return array
     */
    public function getAuthors()
    {
        return $this->authors;
    }



    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }



}