<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\Exception\RendererExceptionInterface;
use Dhii\Output\Exception\TemplateRenderExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Common functionality for objects that can build the WHERE clause for an SQL query.
 *
 * @since [*next-version*]
 */
trait BuildSqlWhereClauseCapableTrait
{
    /**
     * Builds the SQL WHERE clause query string portion.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition    Optional condition instance.
     * @param string[]|Stringable[]           $valueHashMap Optional mapping of term names to their hashes.
     *
     * @throws RendererExceptionInterface       If an error occurred while rendering.
     * @throws TemplateRenderExceptionInterface If the renderer failed to render the expression and context.
     *
     * @return string The SQL WHERE clause query portion.
     */
    protected function _buildSqlWhereClause(
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    ) {
        if ($condition === null) {
            return '';
        }

        $rendered = $this->_renderSqlCondition($condition, $valueHashMap);
        $rendered = $this->_normalizeString($rendered);

        return sprintf('WHERE %s', $rendered);
    }

    /**
     * Renders an expression as an SQL condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $condition    The condition to render.
     * @param string[]|Stringable[]      $valueHashMap Optional mapping of term names to their hashes.
     *
     * @throws RendererExceptionInterface       If an error occurred while rendering.
     * @throws TemplateRenderExceptionInterface If the renderer failed to render the expression and context.
     *
     * @return string|Stringable The rendered condition.
     */
    abstract protected function _renderSqlCondition(LogicalExpressionInterface $condition, array $valueHashMap = []);

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
}
