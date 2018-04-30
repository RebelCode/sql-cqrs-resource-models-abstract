<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can provide SQL field names from a field-column map.
 *
 * @since [*next-version*]
 */
trait SqlFieldNamesAwareTrait
{
    /**
     * Retrieves the SQL query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[]|stdClass|Traversable A list of field names.
     */
    protected function _getSqlFieldNames()
    {
        $map   = $this->_getSqlFieldColumnMap();
        $array = $this->_normalizeArray($map);

        return array_keys($array);
    }

    /**
     * Retrieves the mapping of field names to table columns.
     *
     * @since [*next-version*]
     *
     * @return EntityFieldInterface[]|stdClass|Traversable A map of field names mapping to entity field instances.
     */
    abstract protected function _getSqlFieldColumnMap();

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);
}
