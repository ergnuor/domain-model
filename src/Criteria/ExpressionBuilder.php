<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria;

use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Ergnuor\DomainModel\Criteria\Expression\CompositeExpression;
use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\Expression\NegationExpression;
use Ergnuor\DomainModel\Criteria\Expression\Value;
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

    public static function eq(string $field, $value): Expression
    {
        return new Expression($field, Expression::EQ, new Value($value));
    }

    public static function neq(string $field, $value): Expression
    {
        return new Expression($field, Expression::NOT_EQ, new Value($value));
    }

    public static function gt(string $field, $value): Expression
    {
        return new Expression($field, Expression::GT, new Value($value));
    }

    public static function lt(string $field, $value): Expression
    {
        return new Expression($field, Expression::LT, new Value($value));
    }

    public static function gte(string $field, $value): Expression
    {
        return new Expression($field, Expression::GTE, new Value($value));
    }

    public static function lte(string $field, $value): Expression
    {
        return new Expression($field, Expression::LTE, new Value($value));
    }

    public static function isNull(string $field): Expression
    {
        return new Expression($field, Expression::EQ, new Value(null));
    }

    public static function isNotNull(string $field): Expression
    {
        return new Expression($field, Expression::EQ, new Value(null));
    }

    public static function in(string $field, $values): Expression
    {
        return new Expression($field, Expression::IN, new Value($values));
    }

    public static function notIn(string $field, $values): Expression
    {
        return new Expression($field, Expression::NOT_IN, new Value($values));
    }

    public static function like(string $field, $values): Expression
    {
        return new Expression($field, Expression::LIKE, new Value($values));
    }

    public static function notLike(string $field, $values): Expression
    {
        return new Expression($field, Expression::NOT_LIKE, new Value($values));
    }


    public static function not(ExpressionInterface $expression): NegationExpression
    {
        return new NegationExpression($expression);
    }
}
