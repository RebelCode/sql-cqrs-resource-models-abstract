<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Output\Exception\RendererExceptionInterface;
use Dhii\Output\Exception\TemplateRenderExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Traversable;

/**
 * Provides functionality for building the SET portion of an SQL UPDATE query.
 *
 * @since [*next-version*]
 */
trait BuildSqlUpdateSetCapableTrait
{
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
    protected function _buildSqlUpdateSet($changeSet, array $valueHashMap)
    {
        $changes = [];

        foreach ($changeSet as $_column => $_value) {
            if ($_value instanceof ExpressionInterface) {
                $_value = $this->_renderSqlExpression($_value, $valueHashMap);
            } else {
                $_valueStr = $this->_normalizeString($_value);

                $_value = isset($valueHashMap[$_valueStr])
                    ? $valueHashMap[$_valueStr]
                    : $this->_normalizeSqlValue($_value);
            }

            $changes[] = sprintf('`%1$s` = %2$s', $_column, $_value);
        }

        $updateSet = implode(', ', $changes);

        return $updateSet;
    }

    /**
     * Normalizes an SQL value, quoting it if it's a string.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The input value.
     *
     * @return string The normalized value.
     */
    abstract protected function _normalizeSqlValue($value);

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
