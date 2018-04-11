<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Output\Exception\RendererExceptionInterface;
use Dhii\Output\Exception\TemplateRenderExceptionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use stdClass;
use Traversable;

/**
 * Provides functionality for building SQL column lists.
 *
 * @since [*next-version*]
 */
trait BuildSqlColumnListCapableTrait
{
    /**
     * Builds the SQL column list.
     *
     * @since [*next-version*]
     *
     * @see   EntityFieldInterface
     * @see   TermInterface
     * @see   ExpressionInterface
     *
     * @param array|stdClass|Traversable $columns The columns, as a map of aliases (as keys) mapping to column names,
     *                                            expressions or entity field instances (as values).
     *
     * @return string The built SQL column list.
     */
    protected function _buildSqlColumnList($columns)
    {
        $columnList = [];

        foreach ($columns as $_alias => $_column) {
            if ($_column instanceof TermInterface) {
                $_rColumn = $this->_renderSqlExpression($_column);
            } elseif ($_column instanceof EntityFieldInterface) {
                $_rColumn = $this->_escapeSqlReference($_column->getField(), $_column->getEntity());
            } else {
                $_rColumn = $this->_escapeSqlReference($_column);
            }

            $_escAlias = $this->_escapeSqlReference($_alias);
            $columnList[] = sprintf('%1$s AS %2$s', $_rColumn, $_escAlias);
        }

        return (count($columnList) > 0)
            ? implode(', ', $columnList)
            : '*';
    }

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
     * Renders an SQL expression.
     *
     * @since [*next-version*]
     *
     * @param TermInterface         $expression   The expression to render.
     * @param string[]|Stringable[] $valueHashMap Optional mapping of term names to their hashes.
     *
     * @throws RendererExceptionInterface       If an error occurred while rendering.
     * @throws TemplateRenderExceptionInterface If the renderer failed to render the expression and context.
     *
     * @return string|Stringable The rendered expression.
     */
    abstract protected function _renderSqlExpression(TermInterface $expression, array $valueHashMap = []);
}
