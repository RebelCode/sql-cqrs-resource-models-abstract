<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can escape SQL table and column references.
 *
 * @since [*next-version*]
 */
trait EscapeSqlReferenceListCapableTrait
{
    /**
     * Escapes a reference string, or a list of reference strings, for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]|EntityFieldInterface[]|Traversable $references The references to escape, as a list
     *                                                                             of strings, stringable objects or
     *                                                                             `EntityFieldInterface` instances.
     *
     * @return string The escaped references, as a comma separated string if a list was given.
     */
    protected function _escapeSqlReferenceList($references)
    {
        if (empty($references)) {
            return '';
        }

        $array = (is_string($references) || $references instanceof Stringable)
            ? [$references]
            : $this->_normalizeArray($references);

        $array = array_map(
            function($arg) {
                $entity = null;
                $field = $arg;

                if ($arg instanceof EntityFieldInterface) {
                    $entity = $arg->getEntity();
                    $field = $arg->getField();
                }

                return $this->_escapeSqlReference($entity, $field);
            },
            $array
        );

        return implode(', ', $array);
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
}
