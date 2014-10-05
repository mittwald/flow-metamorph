<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Tca;


class Tca implements \ArrayAccess
{



    /**
     * @var array
     */
    private $tableConfigurationData = [];



    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->tableConfigurationData);
    }



    public function &offsetGet($offset)
    {
        $a =& $this->tableConfigurationData[$offset];
        return $a;
    }



    public function offsetSet($offset, $value)
    {
        $this->tableConfigurationData[$offset] = $value;
    }



    public function offsetUnset($offset)
    {
        unset($this->tableConfigurationData[$offset]);
    }
}