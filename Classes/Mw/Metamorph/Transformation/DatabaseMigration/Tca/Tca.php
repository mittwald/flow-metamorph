<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Tca;

use Helmich\Scalars\Types\AbstractScalar;

class Tca implements \ArrayAccess {

	/**
	 * @var array
	 */
	private $tableConfigurationData = [];

	public function offsetExists($offset) {
		return array_key_exists($this->offsetToPrimitive($offset), $this->tableConfigurationData);
	}

	public function &offsetGet($offset) {
		$a =& $this->tableConfigurationData[$this->offsetToPrimitive($offset)];
		return $a;
	}

	public function offsetSet($offset, $value) {
		$this->tableConfigurationData[$this->offsetToPrimitive($offset)] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->tableConfigurationData[$this->offsetToPrimitive($offset)]);
	}

	private function offsetToPrimitive($offset) {
		if ($offset instanceof AbstractScalar) {
			$offset = $offset->toPrimitive();
		}
		return $offset;
	}
}