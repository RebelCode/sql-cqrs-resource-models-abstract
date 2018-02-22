<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
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
     * @param string|Stringable               $table        The name of the table to delete from.
     * @param LogicalExpressionInterface|null $condition    Optional condition that records must satisfy to be deleted.
     * @param string[]|Stringable[]           $valueHashMap Optional mapping of term names to their hashes
     *
     * @return string The built DELETE query.
     */
    protected function _buildDeleteSql(
        $table,
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    ) {
        $escTable = $this->_escapeSqlReferences($table);
        $where    = $this->_buildSqlWhereClause($condition, $valueHashMap);

        $query = sprintf('DELETE FROM %1$s %2$s', $escTable, $where);
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
     * Escapes a reference string, or a list of reference strings, for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|array|stdClass|Traversable $references The reference strings to escape.
     *
     * @return string The escaped references, as a comma separated string if a list was given.
     */
    abstract protected function _escapeSqlReferences($references);
}
