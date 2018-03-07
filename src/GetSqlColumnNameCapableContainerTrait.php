<?php

namespace RebelCode\Storage\Resource\Sql;

use ArrayAccess;
use Dhii\Exception\InternalExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * Functionality for retrieving the matching column name for a given field name by mapping it via a container.
 *
 * @since [*next-version*]
 */
trait GetSqlColumnNameCapableContainerTrait
{
    /**
     * Retrieves the column name for the given field name.
     *
     * This implementation uses a container to map the field name to a matching column name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $fieldName The field name.
     *
     * @throws InvalidArgumentException If the field name is not a valid string.
     * @throws OutOfBoundsException If no column name could be found for the given field name.
     * @throws InternalExceptionInterface If a problem occurred while trying to retrieve the column name.
     *
     * @return string|Stringable The column name.
     */
    protected function _getSqlColumnName($fieldName)
    {
        $map = $this->_getSqlFieldColumnMap();
        $key = $this->_normalizeString($fieldName);

        try {
            return $this->_containerGet($map, $key);
        } catch (NotFoundExceptionInterface $notFoundException) {
            throw $this->_createOutOfBoundsException(
                $this->__('No column name found for field "%s"', [$fieldName]),
                null,
                $notFoundException,
                $fieldName
            );
        } catch (ContainerExceptionInterface $containerException) {
            throw $this->_createInternalException(
                $this->__('A problem occurred while trying to retrieve the column name'),
                null,
                $containerException
            );
        }
    }

    /**
     * Retrieves the field-column map associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable[]|stdClass|ArrayAccess|ContainerInterface A map of field names mapping to column names.
     */
    abstract protected function _getSqlFieldColumnMap();

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
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Creates a new Dhii Out Of Bounds exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of bounds, if any.
     *
     * @return OutOfBoundsException The new exception.
     */
    abstract protected function _createOutOfBoundsException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Creates a new Internal exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     *
     * @return InternalExceptionInterface The new exception.
     */
    abstract protected function _createInternalException($message = null, $code = null, $previous = null);

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
