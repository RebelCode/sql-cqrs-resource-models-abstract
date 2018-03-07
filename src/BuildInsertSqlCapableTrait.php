<?php

namespace RebelCode\Storage\Resource\Sql;

use ArrayAccess;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can build INSERT SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildInsertSqlCapableTrait
{
    /**
     * Builds an INSERT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $table        The name of the table to insert into.
     * @param array|Traversable $columns      A list of columns names. The order is preserved in the built query.
     * @param array|Traversable $records      The list of record data containers.
     * @param array             $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the row set is empty.
     *
     * @return string The built INSERT query.
     */
    protected function _buildInsertSql($table, $columns, $records, array $valueHashMap = [])
    {
        if (count($records) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('Row set cannot be empty'),
                null,
                null,
                $records
            );
        }

        $tableName   = $this->_escapeSqlReference($table);
        $columnsList = $this->_escapeSqlReferenceList($columns);

        $values = [];
        foreach ($records as $_rowData) {
            $values[] = $this->_buildSqlRecordValues($columns, $_rowData, $valueHashMap);
        }

        $query = sprintf(
            'INSERT INTO %1$s (%2$s) VALUES %3$s',
            $tableName,
            $columnsList,
            implode(', ', $values)
        );

        return sprintf('%s;', trim($query));
    }

    /**
     * Builds the values for a single record.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable                             $columns      The list of table columns.
     * @param array|ArrayAccess|stdClass|ContainerInterface $record       The record data container.
     * @param array                                         $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException    If the record data container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the record data container.
     * @throws OutOfRangeException         If a column name is invalid.
     *
     * @return string The build row values as a comma separated list in parenthesis.
     */
    abstract protected function _buildSqlRecordValues($columns, $record, array $valueHashMap = []);

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

    /**
     * Escapes a reference string, or a list of reference strings, for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]|EntityFieldInterface[]|Traversable $references The references to escape, as a list
     *                                                                             of strings, stringable objects or
     *                                                                             `EntityFieldInterface` instances.
     *
     * @return string The escaped references, as a comma separated string if a list was given.
     */
    abstract protected function _escapeSqlReferenceList($references);

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
