<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of an SQL field-to-column map.
 *
 * @since [*next-version*]
 */
trait SqlFieldColumnMapAwareTrait
{
    /**
     * The field to column map, with field names as keys and column names as values.
     *
     * Allowed values are: string, Stringable, TermInterface, EntityFieldInterface
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $fieldColumnMap;

    /**
     * Retrieves the field-column map associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable[] A map of field names mapping to columns. Column values may be: string, Stringable,
     *                             TermInterface or EntityFieldInterface
     */
    protected function _getSqlFieldColumnMap()
    {
        return $this->fieldColumnMap;
    }

    /**
     * Sets the field-column map for this instance.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $map The field-to-column map. Allowed values are: string, Stringable,
     *                                        TermInterface, EntityFieldInterface
     *
     * @throws InvalidArgumentException If the argument is not a valid map.
     */
    protected function _setSqlFieldColumnMap($map)
    {
        $this->fieldColumnMap = $this->_normalizeIterable($map);
    }

    /**
     * Normalizes an iterable.
     *
     * Makes sure that the return value can be iterated over.
     *
     * @since [*next-version*]
     *
     * @param mixed $iterable The iterable to normalize.
     *
     * @throws InvalidArgumentException If the iterable could not be normalized.
     *
     * @return array|stdClass|Traversable The normalized iterable.
     */
    abstract protected function _normalizeIterable($iterable);

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
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
