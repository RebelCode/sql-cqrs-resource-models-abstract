<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\InternalExceptionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use stdClass;
use Traversable;

/**
 * Common functionality for building GROUP BY query portions.
 *
 * @since [*next-version*]
 */
trait BuildSqlGroupByClauseCapableTrait
{
    /**
     * Builds the GROUP BY portion of the an SQL query.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $grouping A list of strings, stringable objects or entity-field instances.
     *
     * @return string The built GROUP BY query portion.
     *
     * @throws InvalidArgumentException   If the argument is not a valid iterable.
     * @throws OutOfRangeException        If an element in the iterable is invalid.
     * @throws InternalExceptionInterface If a problem occurred while trying to retrieve a column name.
     */
    protected function _buildSqlGroupByClause($grouping = [])
    {
        $grouping = $this->_normalizeIterable($grouping);
        $parts    = [];

        foreach ($grouping as $_groupField) {
            // Get entity (if any) and field
            $_entity = ($_groupField instanceof EntityFieldInterface)
                ? $_groupField->getEntity()
                : null;
            $_field  = ($_groupField instanceof EntityFieldInterface)
                ? $_groupField->getField()
                : $_groupField;

            try {
                // Change field to column
                $_column = $this->_getSqlColumnName($_field);
                // Generate SQL escaped entity field string
                $_escaped = $this->_escapeSqlReference($_column, $_entity);
            } catch (InvalidArgumentException $exception) {
                throw $this->_createOutOfRangeException(
                    $this->__('An element in the grouping iterable is invalid'), null, null, $_groupField
                );
            }

            $parts[] = $_escaped;
        }

        return (count($parts) > 0)
            ? sprintf('GROUP BY %s', implode(', ', $parts))
            : '';
    }

    /**
     * Retrieves the column name for the given field name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $fieldName The field name.
     *
     * @throws InvalidArgumentException   If the field name is not a valid string.
     * @throws OutOfBoundsException       If no column name could be found for the given field name.
     * @throws InternalExceptionInterface If a problem occurred while trying to retrieve the column name.
     *
     * @return string|Stringable The column name.
     */
    abstract protected function _getSqlColumnName($fieldName);

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

    /**
     * Creates a new Dhii Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
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
