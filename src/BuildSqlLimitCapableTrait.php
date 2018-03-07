<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Common functionality for building the LIMIT potion of an SQL query.
 *
 * @since [*next-version*]
 */
trait BuildSqlLimitCapableTrait
{
    /**
     * Builds the LIMIT portion of an SQL query.
     *
     * @since [*next-version*]
     *
     * @param int $limit The number of records to limit to.
     *
     * @throws InvalidArgumentException If the argument is not a valid integer.
     * @throws OutOfRangeException      If the argument is a negative integer.
     *
     * @return string The built LIMIT query portion.
     */
    protected function _buildSqlLimit($limit = null)
    {
        $limit = $this->_normalizeInt($limit);

        if ($limit < 0) {
            throw $this->_createOutOfRangeException(
                $this->__('Limit cannot be negative'),
                null,
                null,
                $limit
            );
        }

        return sprintf('LIMIT %d', $limit);
    }

    /**
     * Normalizes a value into an integer.
     *
     * The value must be a whole number, or a string representing such a number,
     * or an object representing such a string.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|float|int $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return int The normalized value.
     */
    abstract protected function _normalizeInt($value);

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
