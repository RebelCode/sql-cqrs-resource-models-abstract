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
    protected function _escapeSqlReference($reference, $prefix = null)
    {
        if ($reference !== null && strlen($reference) === 0) {
            throw $this->_createOutOfRangeException(
                $this->__('Reference string cannot be an empty'),
                null,
                null,
                $reference
            );
        }

        $escResult = sprintf('`%s`', $this->_normalizeString($reference));

        // If prefix given, escape and add to result
        if ($prefix !== null && strlen($prefix)) {
            $escPrefix = sprintf('`%s`', $this->_normalizeString($prefix));
            $escResult = sprintf('%1$s.%2$s', $escPrefix, $escResult);
        }

        return $escResult;
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
