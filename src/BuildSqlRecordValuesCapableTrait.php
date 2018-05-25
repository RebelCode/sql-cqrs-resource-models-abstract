<?php

namespace RebelCode\Storage\Resource\Sql;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Traversable;

/**
 * Provides functionality for building record VALUES for INSERT SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildSqlRecordValuesCapableTrait
{
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
    protected function _buildSqlRecordValues($columns, $record, array $valueHashMap = [])
    {
        $data = [];

        foreach ($columns as $_columnName) {
            try {
                // Get row data for this column
                $_value = $this->_containerGet($record, $_columnName);

                // If value is a scalar, attempt to change it into a hash
                if (is_scalar($_value)) {
                    // Attempt to change into a hash
                    $_valueKey = $this->_normalizeString($_value);
                    $_value    = isset($valueHashMap[$_valueKey])
                        ? $valueHashMap[$_valueKey]
                        : $this->_normalizeSqlValue($_value);
                } else {
                    // Otherwise, simply normalize it
                    $_value = $this->_normalizeSqlValue($_value);
                }
            } catch (NotFoundExceptionInterface $notFoundException) {
                $_value = 'DEFAULT';
            }

            $data[$_columnName] = $_value;
        }

        return sprintf('(%s)', implode(', ', $data));
    }

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Normalizes an SQL value, quoting it if it's a string.
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $value  The input value.
     *
     * @return string The normalized value.
     */
    abstract protected function _normalizeSqlValue($value);
}
