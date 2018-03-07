<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\InternalExceptionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Traversable;

/**
 * Common functionality for objects that can build DELETE SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildDeleteSqlCapableTrait
{
    /**
     * Builds a DELETE SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to delete from.
     * @param LogicalExpressionInterface|null   $condition    The condition that records must satisfy to be deleted.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param int|null                          $offset       The number of records to offset by, zero-based.
     * @param string[]|Stringable[]             $valueHashMap The mapping of term names to their hashes
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built DELETE query.
     */
    protected function _buildDeleteSql(
        $table,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $valueHashMap = []
    ) {
        $escTable = $this->_escapeSqlReference($table);
        $where    = $this->_buildSqlWhereClause($condition, $valueHashMap);

        $sOrder = ($ordering !== null)
            ? $this->_buildSqlOrderBy($ordering)
            : '';
        $sLimit = ($limit !== null)
            ? $this->_buildSqlLimit($limit)
            : '';
        $sOffset = ($limit !== null && $offset !== null)
            ? $this->_buildSqlOffset($offset)
            : '';

        $query = sprintf('DELETE FROM %1$s %2$s %3$s %4$s %5$s', $escTable, $where, $sOrder, $sLimit, $sOffset);
        $query = sprintf('%s;', trim($query));

        return $query;
    }

    /**
     * Builds the SQL WHERE clause query string portion.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition    Optional condition instance.
     * @param string[]|Stringable[]           $valueHashMap Optional mapping of term names to their hashes.
     *
     * @return string The SQL WHERE clause query portion.
     */
    abstract protected function _buildSqlWhereClause(
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    );

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
     * Builds the OFFSET portion of an SQL query.
     *
     * @since [*next-version*]
     *
     * @param int $offset The number of records to offset by.
     *
     * @throws InvalidArgumentException If the argument is not a valid integer.
     * @throws OutOfRangeException      If the argument is a negative integer.
     *
     * @return string The built OFFSET query portion.
     */
    abstract protected function _buildSqlOffset($offset = null);

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
}
