<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Provides SQL value normalization functionality.
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
     * @param mixed $value The input value.
     *
     * @return string The normalized value.
     */
    protected function _normalizeSqlValue($value)
    {
        return (is_string($value) || $value instanceof Stringable)
            ? sprintf('"%s"', $this->_normalizeString($value))
            : $value;
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
}
