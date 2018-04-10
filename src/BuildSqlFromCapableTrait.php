<?php

namespace RebelCode\Storage\Resource\Sql;

use InvalidArgumentException;
use OutOfRangeException;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Functionality for building the FROM portion of an SQL query.
 *
 * @since [*next-version*]
 */
trait BuildSqlFromCapableTrait
{
    /**
     * Builds the SQL FROM section.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $tables A mapping of tables names (keys) to their aliases (values). Null
     *                                           aliases may be given for no aliasing.
     *
     * @return string The build SQL table FROM section.
     */
    protected function _buildSqlFrom($tables)
    {
        $parts = [];

        foreach ($tables as $_table => $_alias) {
            $_part = $this->_escapeSqlReference($_table);

            if ($_alias !== null) {
                $_part = sprintf('%1$s as %2$s', $_part, $this->_escapeSqlReference($_alias));
            }

            $parts[] = $_part;
        }

        $imploded = implode(', ', $parts);

        return sprintf('FROM %s', $imploded);
    }

    /**
     * Escapes an SQL reference with an optional prefix.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable      $reference The reference string.
     * @param string|Stringable|null $prefix    The reference prefix, if any.
     *
     * @throws InvalidArgumentException If either argument is not a valid string.
     * @throws OutOfRangeException      If an invalid string is given as argument.
     *
     * @return string The escaped string.
     */
    abstract protected function _escapeSqlReference($reference, $prefix = null);
}
