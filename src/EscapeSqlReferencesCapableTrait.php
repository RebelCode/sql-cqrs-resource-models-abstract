<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can escape SQL table and column references.
 *
 * @since [*next-version*]
 */
trait EscapeSqlReferencesCapableTrait
{
    /**
     * Escapes a reference string, or a list of reference strings, for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|array|stdClass|Traversable $references The reference strings to escape.
     *
     * @return string The escaped references, as a comma separated string if a list was given.
     */
    protected function _escapeSqlReferences($references)
    {
        if (empty($references)) {
            return '';
        }

        $array = (is_string($references) || $references instanceof Stringable)
            ? [$references]
            : $this->_normalizeArray($references);

        $commaList = implode('`, `', $array);
        $escaped   = sprintf('`%s`', $commaList);

        return $escaped;
    }

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);
}
