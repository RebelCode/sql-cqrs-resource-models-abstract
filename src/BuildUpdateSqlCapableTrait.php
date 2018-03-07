<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\InternalExceptionInterface;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use Traversable;

/**
 * Common functionality for objects that can build UPDATE SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildUpdateSqlCapableTrait
{
    /**
     * Builds a UPDATE SQL query.
     *
     * Consider using a countable argument for the $changeSet parameter for better performance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to insert into.
     * @param array|TermInterface[]|Traversable $changeSet    The change set, mapping field names to their new values
     *                                                        or value expressions.
     * @param LogicalExpressionInterface|null   $condition    Optional WHERE clause condition.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param array                             $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the change set is empty.
     *
     * @return string The built UPDATE query.
     */
    protected function _buildUpdateSql(
        $table,
        $changeSet,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        array $valueHashMap = []
    ) {
        if ($this->_countIterable($changeSet) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('Change set cannot be empty'),
                null,
                null,
                $changeSet
            );
        }

        $tableName = $this->_escapeSqlReference($table);
        $updateSet = $this->_buildSqlUpdateSet($changeSet, $valueHashMap);
        $where = $this->_buildSqlWhereClause($condition, $valueHashMap);

        $sOrder = ($ordering !== null)
            ? $this->_buildSqlOrderBy($ordering)
            : '';
        $sLimit = ($limit !== null)
            ? $this->_buildSqlLimit($limit)
            : '';

        $parts = array_filter([$where, $sOrder, $sLimit], 'strlen');
        $tail = implode(' ', $parts);

        $query = sprintf(
            'UPDATE %1$s SET %2$s %3$s',
            $tableName,
            $updateSet,
            $tail
        );

        return sprintf('%s;', trim($query));
    }

    /**
     * Builds the SET portion of an SQL UPDATE query.
     *
     * @since [*next-version*]
     *
     * @param array|ExpressionInterface[]|Traversable $changeSet    The change set, mapping field names to their new
     *                                                              values or value expressions.
     * @param array                                   $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The built SET portion string.
     */
    abstract protected function _buildSqlUpdateSet($changeSet, array $valueHashMap);

    /**
     * Builds the ORDER BY portion of a query from `OrderInterface` instances.
     *
     * @since [*next-version*]
     *
     * @param OrderInterface[]|Traversable $ordering The `OrderInterface` instances.
     *
     * @throws OutOfRangeException        If the argument contains an invalid element.
     * @throws InternalExceptionInterface If a problem occurred while trying to get the column name for a field name.
     *
     * @return string The built ORDER BY query portion string, or an empty string if an empty $orders list is given.
     */
    abstract protected function _buildSqlOrderBy($ordering);

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
    abstract protected function _buildSqlLimit($limit = null);

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
    abstract protected function _escapeSqlReference($reference, $prefix = null);

    /**
     * Builds the SQL WHERE clause query string portion.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition    Optional condition instance.
     * @param array                           $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The SQL WHERE clause query portion.
     */
    abstract protected function _buildSqlWhereClause(
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    );

    /**
     * Counts the elements in an iterable.
     *
     * Is optimized to retrieve count from values that support it.
     * - If array, will count in regular way using count();
     * - If {@see Countable}, will do the same;
     * - If {@see IteratorAggregate}, will drill down into internal iterators
     * until the first {@see Countable} is encountered, in which case the same
     * as above will be done.
     * - In any other case, will apply {@see iterator_count()}, which means
     * that it will iterate over the whole traversable to determine the count.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $iterable The iterable to count. Must be finite.
     *
     * @return int The amount of elements.
     */
    abstract protected function _countIterable($iterable);

    /**
     * Creates a new Dhii invalid argument exception.
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
