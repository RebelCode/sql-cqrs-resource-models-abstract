<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Exception as RootException;

/**
 * Common functionality for escaping SQL references, with the ability to escape entity-field pairs.
 *
 * @since [*next-version*]
 */
trait EscapeSqlReferenceCapableTrait
{
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
    protected function _escapeSqlReference($entity, $field)
    {
        if ($entity !== null && strlen($entity) === 0) {
            throw $this->_createOutOfRangeException(
                $this->__('Entity argument cannot be an empty string'),
                null,
                null,
                $entity
            );
        }

        if ($field !== null && strlen($field) === 0) {
            throw $this->_createOutOfRangeException(
                $this->__('Field argument cannot be an empty string'),
                null,
                null,
                $field
            );
        }

        $entity = ($entity !== null)
            ? sprintf('`%s`', $this->_normalizeString($entity))
            : null;

        $field = sprintf('`%s`', $this->_normalizeString($field));

        // If entity given, yield the combined escaped strings
        if ($entity !== null && $field !== null) {
            return sprintf('%1$s.%2$s', $entity, $field);
        }

        return $field;
    }

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
