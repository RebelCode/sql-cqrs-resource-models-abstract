<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Storage\Resource\Sql\EntityAwareInterface as EntityAware;
use Dhii\Storage\Resource\Sql\FieldAwareInterface as FieldAware;
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
     * @param string|Stringable|EntityAware|FieldAware|array|Traversable $references The references to escape, either a
     *                                                                               single entity-aware, field-aware,
     *                                                                               entity-and-field-aware, string or
     *                                                                               stringable argument, or a list of
     *                                                                               similar values.
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

        $array = array_map(
            function($arg) {
                $entity = $arg instanceof EntityAware
                    ? $arg->getEntity()
                    : null;
                $field = $arg instanceof FieldAware
                    ? $arg->getField()
                    : null;

                // If both entity-aware and field-aware, yield the combined escaped strings
                if ($entity !== null && $field !== null) {
                    return sprintf('`%1$s`.`%2$s`', $entity, $field);
                }

                // If entity-aware and not field-aware, handle as a field-aware
                $field = ($field === null && $entity !== null)
                    ? $entity
                    : $field;

                // If not field-aware, use the argument as the field
                $str = ($field !== null)
                    ? $field
                    : $arg;

                return sprintf('`%s`', $str);
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
}
