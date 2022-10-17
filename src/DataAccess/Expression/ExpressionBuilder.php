<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use function func_get_args;

class ExpressionBuilder
{
    private function __construct()
    {
    }

    /**
     * @param ExpressionInterface ...$x
     *
     * @return CompositeExpression
     */
    public static function andX($x = null): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_AND, func_get_args());
    }

    /**
     * @param ExpressionInterface ...$x
     *
     * @return CompositeExpression
     */
    public static function orX($x = null): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_OR, func_get_args());
    }

    public static function eq(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::EQ, new Value($value));
    }

    public static function gt(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::GT, new Value($value));
    }

    public static function lt(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::LT, new Value($value));
    }

    public static function gte(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::GTE, new Value($value));
    }

    public static function lte(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::LTE, new Value($value));
    }

    public static function neq(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::NEQ, new Value($value));
    }

    public static function isNull(string $field): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::EQ, new Value(null));
    }

    public static function in(string $field, $values): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::IN, new Value($values));
    }

    public static function notIn(string $field, $values): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::NIN, new Value($values));
    }

    public static function contains(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::CONTAINS, new Value($value));
    }

    public static function notContains(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::NCONTAINS, new Value($value));
    }

    public static function memberOf(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::MEMBER_OF, new Value($value));
    }

    public static function startsWith(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::STARTS_WITH, new Value($value));
    }

    public static function notStartsWith(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::NSTARTS_WITH, new Value($value));
    }

    public static function endsWith(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::ENDS_WITH, new Value($value));
    }

    public static function notEndsWith(string $field, $value): ComparisonExpression
    {
        return new ComparisonExpression($field, ComparisonExpression::NENDS_WITH, new Value($value));
    }

    public static function not(ExpressionInterface $expression): NegationExpression
    {
        return new NegationExpression($expression);
    }
}
