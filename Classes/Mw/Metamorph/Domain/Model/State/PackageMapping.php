<?php
namespace Mw\Metamorph\Domain\Model\State;


class PackageMapping implements \JsonSerializable
{



    const ACTION_MORPH = 'MORPH';


    const ACTION_IGNORE = 'IGNORE';


    private $extensionKey;


    private $packageKey;


    private $filePath;


    private $action = self::ACTION_MORPH;


    private $description;


    private $version;


    private $authors = [];



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



    public function jsonSerialize()
    {
        return [
            'path'        => $this->filePath,
            'action'      => $this->action,
            'packageKey'  => $this->packageKey,
            'description' => $this->description,
            'version'     => $this->version,
            'authors'     => $this->authors
        ];
    }



    static public function jsonUnserialize(array $data, $extensionKey = NULL)
    {
        // Note to self: DO NOT write "new self" here. Flow's proxy generation will kick you in the balls for that.
        $mapping = new PackageMapping($data['path'], $extensionKey);

        $mapping->packageKey  = $data['packageKey'];
        $mapping->action      = $data['action'];
        $mapping->description = $data['description'];
        $mapping->version     = $data['version'];
        $mapping->authors     = $data['authors'];
        return $mapping;
    }
}