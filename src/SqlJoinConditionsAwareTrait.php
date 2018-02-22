<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of SQL JOIN conditions.
 *
 * @since [*next-version*]
 */
trait SqlJoinConditionsAwareTrait
{
    /**
     * The join conditions, with table names as keys.
     *
     * @since [*next-version*]
     *
     * @var LogicalExpressionInterface[]
     */
    protected $joinConditions;

    /**
     * Retrieves the JOIN conditions associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return LogicalExpressionInterface[] The join conditions, keyed by table name.
     */
    protected function _getSqlJoinConditions()
    {
        return $this->joinConditions;
    }

    /**
     * Sets the SQL JOIN conditions for this instance.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface[]|stdClass|Traversable $joinConditions The JOIN conditions, keyed by table.
     *
     * @throws InvalidArgumentException If the argument contains an invalid key or value.
     */
    protected function _setSqlJoinConditions($joinConditions)
    {
        $this->joinConditions = $this->_normalizeArray($joinConditions);
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
