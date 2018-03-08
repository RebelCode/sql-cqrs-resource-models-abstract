<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\InternalExceptionInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use Traversable;
use Exception as RootException;

/**
 * Common functionality for building ORDER BY query portions.
 *
 * @since [*next-version*]
 */
trait BuildSqlOrderByCapableTrait
{
    /**
     * Builds the ORDER BY portion of a query from `OrderInterface` instances.
     *
     * @since [*next-version*]
     *
     * @param OrderInterface[]|Traversable $ordering The `OrderInterface` instances.
     *
     * @throws OutOfRangeException        If the argument contains an invalid element.
     * @throws InternalExceptionInterface If a problem occurred while trying to get the column name for a field name.
     *
     * @return string The built ORDER BY query portion string, or an empty string if an empty $orders list is given.
     */
    protected function _buildSqlOrderBy($ordering)
    {
        $orderParts = [];

        foreach ($ordering as $_order) {
            if (!($_order instanceof OrderInterface)) {
                throw $this->_createOutOfRangeException(
                    $this->__('Argument contains a non-OrderInterface element'),
                    null,
                    null,
                    $ordering
                );
            }

            $entity = $_order->getEntity();
            $field  = $_order->getField();
            try {
                $column = $this->_getSqlColumnName($field);
            } catch (OutOfBoundsException $outOfBoundsException) {
                $column = $field;
            }
            $mode = $_order->isAscending()
                ? 'ASC'
                : 'DESC';

            try {
                $entityField = $this->_escapeSqlReference($column, $entity);
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw $this->_createOutOfRangeException(
                    $this->__('Argument contains an OrderInterface element with invalid entity field information'),
                    null,
                    $invalidArgumentException,
                    $ordering
                );
            }

            $orderParts[] = sprintf('%1$s %2$s', $entityField, $mode);
        }

        if (empty($orderParts)) {
            return '';
        }

        return sprintf('ORDER BY %s', implode(', ', $orderParts));
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
