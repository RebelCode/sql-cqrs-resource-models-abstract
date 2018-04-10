<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\InternalExceptionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can build SQL SELECT queries.
 *
 * @since [*next-version*]
 */
trait BuildSelectSqlCapableTrait
{
    /**
     * Builds a SELECT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]             $columns        A list of names of columns to select.
     * @param array|stdClass|Traversable        $tables         A mapping of tables names (keys) to their aliases
     *                                                          (values). Null aliases may be given for no aliasing.
     * @param LogicalExpressionInterface[]      $joinConditions Optional list of JOIN conditions, keyed by table name.
     * @param LogicalExpressionInterface|null   $whereCondition Optional WHERE condition.
     * @param OrderInterface[]|Traversable|null $ordering       The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit          The number of records to limit the query to.
     * @param int|null                          $offset         The number of records to offset by, zero-based.
     * @param array                             $valueHashMap   Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built SQL query string.
     */
    protected function _buildSelectSql(
        array $columns,
        array $tables,
        array $joinConditions = [],
        LogicalExpressionInterface $whereCondition = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $valueHashMap = []
    ) {
        if ($this->_countIterable($tables) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('No tables were given'),
                null,
                null,
                $tables
            );
        }

        $columnList = (count($columns) > 0)
            ? $this->_escapeSqlReferenceList($columns)
            : '*';

        $from = $this->_buildSqlFrom($tables);
        $joins = $this->_buildSqlJoins($joinConditions, $valueHashMap);
        $where = $this->_buildSqlWhereClause($whereCondition, $valueHashMap);

        $sOrder = ($ordering !== null)
            ? $this->_buildSqlOrderBy($ordering)
            : '';
        $sLimit = ($limit !== null)
            ? $this->_buildSqlLimit($limit)
            : '';
        $sOffset = ($limit !== null && $offset !== null)
            ? $this->_buildSqlOffset($offset)
            : '';

        $parts = array_filter([$from, $joins, $where, $sOrder, $sLimit, $sOffset], 'strlen');
        $tail = implode(' ', $parts);
        $query = sprintf(
            'SELECT %1$s %2$s;',
            $columnList,
            $tail
        );

        return $query;
    }

    /**
     * Builds the SQL FROM section.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $tables A mapping of tables names (keys) to their aliases (values). Null
     *                                           aliases may be given for no aliasing.
     *
     * @return string The build SQL table FROM section.
     */
    abstract protected function _buildSqlFrom($tables);

    /**
     * Builds an SQL JOIN clause from a list of join conditions.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface[]|Traversable $joinConditions A list of JOIN conditions, keyed by table name.
     * @param string[]|Stringable[]                    $valueHashMap   Optional mapping of term names to their hashes.
     *
     * @return string|string The built SQL JOIN clause.
     */
    abstract protected function _buildSqlJoins(array $joinConditions, array $valueHashMap = []);

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
    abstract protected function _escapeSqlReferenceList($references);

    /**
     * Counts the elements in an iterable.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $iterable The iterable to count. Must be finite.
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
