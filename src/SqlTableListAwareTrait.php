<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of a list of SQL table names.
 *
 * @since [*next-version*]
 */
trait SqlTableListAwareTrait
{
    /**
     * An array of table names.
     *
     * @since [*next-version*]
     *
     * @var string[]|Stringable[]
     */
    protected $sqlTableList;

    /**
     * Retrieves the SQL table list associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] The SQL tables names.
     */
    protected function _getSqlTableList()
    {
        return $this->sqlTableList;
    }

    /**
     * Sets the SQL table list for this instance.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]|Traversable $tables The SQL tables names.
     */
    protected function _setSqlTableList($tables)
    {
        $this->sqlTableList = $this->_normalizeArray($tables);
    }

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
