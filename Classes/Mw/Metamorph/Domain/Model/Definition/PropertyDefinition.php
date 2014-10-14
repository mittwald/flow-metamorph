<?php
namespace Mw\Metamorph\Domain\Model\Definition;


class PropertyDefinition
{



    private $name;


    private $docComment;



    public function __construct($name, $docComment = NULL)
    {
        $this->name       = $name;
        $this->docComment = $docComment;
    }



    /**
     * @return mixed
     */
    public function getDocComment()
    {
        return $this->docComment;
    }



    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }



}