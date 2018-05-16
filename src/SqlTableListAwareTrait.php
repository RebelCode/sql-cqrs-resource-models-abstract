<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of a list of SQL table names that map to their aliases.
 *
 * @since [*next-version*]
 */
trait SqlTableListAwareTrait
{
    /**
     * An array of table names, mapping to their aliases.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $sqlTableList;

    /**
     * Retrieves the SQL table list associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable The SQL tables names (keys) mapping to their aliases (values).
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
     * @param array|stdClass|Traversable $tables The SQL tables names (keys) mapping to their aliases (values).
     */
    protected function _setSqlTableList($tables)
    {
        $this->sqlTableList = $this->_normalizeIterable($tables);
    }

    /**
     * Normalizes an iterable.
     *
     * Makes sure that the return value can be iterated over.
     *
     * @since [*next-version*]
     *
     * @param mixed $iterable The iterable to normalize.
     *
     * @throws InvalidArgumentException If the iterable could not be normalized.
     *
     * @return array|stdClass|Traversable The normalized iterable.
     */
    abstract protected function _normalizeIterable($iterable);
}
