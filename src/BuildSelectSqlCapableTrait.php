<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
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
     * @param string[]|Stringable[]           $columns        A list of names of columns to select.
     * @param array                           $tables         A list of names of tables to select from.
     * @param LogicalExpressionInterface[]    $joinConditions Optional list of JOIN conditions, keyed by table name.
     * @param LogicalExpressionInterface|null $whereCondition Optional WHERE condition.
     * @param array                           $valueHashMap   Optional map of value names and their hashes.
     *
     * @return string The built SQL query string.
     */
    protected function _buildSelectSql(
        array $columns,
        array $tables,
        array $joinConditions = [],
        LogicalExpressionInterface $whereCondition = null,
        array $valueHashMap = []
    ) {
        if (count($tables) === 0) {
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

        $tableList = $this->_escapeSqlReferenceList($tables);
        $joins     = $this->_buildSqlJoins($joinConditions, $valueHashMap);
        $where     = $this->_buildSqlWhereClause($whereCondition, $valueHashMap);

        $query = sprintf(
            'SELECT %1$s FROM %2$s %3$s %4$s;',
            $columnList,
            $tableList,
            $joins,
            $where
        );

        return $query;
    }

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
