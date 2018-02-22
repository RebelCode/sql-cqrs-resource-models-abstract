<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Exception as RootException;

/**
 * Provides SQL value normalization functionality, without any column-specific normalization.
 *
 * @since [*next-version*]
 */
trait NormalizeSqlValueCapableTrait
{
    /**
     * Normalizes an SQL value, quoting it if it's a string.
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $value  The input value.
     * @param string|Stringable|null           $column Optional column name, used for normalizing for a specific
     *                                                 column's type.
     *
     * @return string The normalized value.
     *
     * @throws OutOfRangeException If the value cannot be normalized.
     */
    protected function _normalizeSqlValue($value, $column = null)
    {
        if (is_string($value) || $value instanceof Stringable) {
            $str = $this->_normalizeString($value);

            return is_numeric($str)
                ? $str
                : sprintf('"%s"', $str);
        }

        if (is_scalar($value)) {
            return $value;
        }

        throw $this->_createOutOfRangeException(
            $this->__('Argument cannot be normalized to an SQL value'),
            null,
            null,
            $value
        );
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
