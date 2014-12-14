<?php
namespace Mw\Metamorph\Transformation\DatabaseMigration\Tca;

use Helmich\Scalars\Types\ArrayList;
use Helmich\Scalars\Types\String;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinition;
use Mw\Metamorph\Domain\Model\Definition\ClassDefinitionContainer;
use TYPO3\Flow\Annotations as Flow;

/**
 * TCA helper class that maps table to class names and vice versa.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\DatabaseMigration\Tca
 */
class MappingHelper {

	/**
	 * @var ClassDefinitionContainer
	 * @Flow\Inject
	 */
	protected $classDefinitionContainer;

	/**
	 * @var \ArrayAccess
	 */
	private $tca;

	/**
	 * Constructs a new instance.
	 *
	 * @param \ArrayAccess $tca The TCA container.
	 */
	public function __construct(\ArrayAccess $tca) {
		$this->tca = $tca;
	}

	/**
	 * Gets all possible table names for a class.
	 *
	 * @param \Helmich\Scalars\Types\String $newClassName The new class name.
	 * @param \Helmich\Scalars\Types\String $oldClassName The old class name.
	 * @return ArrayList A list of possible table names.
	 */
	public function getPossibleTableNamesForClass(String $newClassName, String $oldClassName) {
		return new ArrayList(
			[
				$newClassName->toLower()->replace('\\', '_'),
				$newClassName->toLower()->split('\\')->set(0, 'tx')->join('_'),
				$oldClassName->toLower()->replace('\\', '_'),
				$oldClassName->toLower()->split('\\')->set(0, 'tx')->join('_')
			]
		);
	}

	/**
	 * Maps a class property name to column name (this is basically a camelCase to lower_case conversion).
	 *
	 * @param \Helmich\Scalars\Types\String $propertyName The property name.
	 * @return String The column name.
	 */
	public function propertyToColumnName(String $propertyName) {
		return $propertyName->regexReplace(',([A-Z]),', '_$1')->toLower()->strip('_');
	}

	/**
	 * Maps a column name to a class property name (basically a lower_case to camelCase conversion).
	 *
	 * @param \Helmich\Scalars\Types\String $columnName The column name.
	 * @return String The property name.
	 */
	public function columnNameToProperty(String $columnName) {
		return $columnName
			->split('_')
			->mapWithKey(function ($index, String $part) { return $index === 0 ? $part : $part->toCamelCase(); })
			->join('');
	}

	/**
	 * Gets the TCA and table name for a given class.
	 *
	 * Output parameters are passed and set by reference, not return value!
	 *
	 * @param \Helmich\Scalars\Types\String $newClassName The new class name.
	 * @param \Helmich\Scalars\Types\String $oldClassName The old class name
	 * @param array                         $tca          The TCA array.
	 * @param string                        $tableName    The table name.
	 * @return void
	 */
	public function getTcaForClass(String $newClassName, String $oldClassName, array &$tca, &$tableName) {
		$possibleNames = $this->getPossibleTableNamesForClass($newClassName, $oldClassName);
		foreach ($possibleNames as $name) {
			if (isset($this->tca[$name])) {
				$tca       = $this->tca[$name];
				$tableName = $name;
			}
		}
	}

	/**
	 * Gets the class definition for a given table.
	 *
	 * @param string $tableName The table name.
	 * @return ClassDefinition The class definition.
	 */
	public function getClassForTable($tableName) {
		$filterFunction = function (ClassDefinition $definition) use ($tableName) {
			$possibleTableNames = $this->getPossibleTableNamesForClass(
				new String($definition->getFullyQualifiedName()),
				new String($definition->getClassMapping()->getOldClassName())
			);

			return $possibleTableNames->contains($tableName);
		};

		$classDefinitions = $this->classDefinitionContainer->findByFilter($filterFunction);
		return (count($classDefinitions) > 0) ? $classDefinitions[0] : NULL;
	}

}