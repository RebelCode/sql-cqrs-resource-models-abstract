<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
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
     * @param OrderInterface[]|Traversable $orders The `OrderInterface` instances.
     *
     * @return string The built ORDER BY query portion string, or an empty string if an empty $orders list is given.
     */
    protected function _buildSqlOrderBy($orders)
    {
        $orderParts = [];

        foreach ($orders as $_order) {
            if (!($_order instanceof OrderInterface)) {
                throw $this->_createOutOfRangeException(
                    $this->__('Argument contains a non-OrderInterface element'),
                    null,
                    null,
                    $orders
                );
            }

            $entity = $_order->getEntity();
            $field = $_order->getField();
            $mode = $_order->isAscending()
                ? 'ASC'
                : 'DESC';

            try {
                $entityField = $this->_escapeSqlReference($entity, $field);
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw $this->_createOutOfRangeException(
                    $this->__('Argument contains an OrderInterface element with invalid entity field information'),
                    null,
                    $invalidArgumentException,
                    $orders
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
     * Escapes an SQL reference, optionally scoped to a particular entity.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $entity The reference entity name, if any.
     * @param string|Stringable      $field  The reference field name.
     *
     * @return string The escaped string.
     *
     * @throws InvalidArgumentException If either argument is not a valid string.
     * @throws OutOfRangeException If an invalid string is given as argument.
     */
    abstract protected function _escapeSqlReference($entity, $field);

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
