<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;
use Doctrine\ORM\Query\Expr;

class DoctrineBasicExpressionMapper implements BasicExpressionMapperInterface
{
    private static array $operatorMap = [
        ComparisonExpression::GT => Expr\Comparison::GT,
        ComparisonExpression::GTE => Expr\Comparison::GTE,
        ComparisonExpression::LT => Expr\Comparison::LT,
        ComparisonExpression::LTE => Expr\Comparison::LTE
    ];

    private Expr $expr;

    public function __construct()
    {
        $this->expr = new Expr();
    }

    public function orX(...$x): Expr\Orx
    {
        return $this->expr->orX(...$x);
    }

    public function andX(...$x): Expr\Andx
    {
        return $this->expr->andX(...$x);
    }

    public function eq(string $field, string $placeholder): Expr\Comparison
    {
        return $this->expr->eq($field, $placeholder);
    }

    public function neq(string $field, string $placeholder): Expr\Comparison
    {
        return $this->expr->neq($field, $placeholder);
    }

    public function like(string $field, string $placeholder): Expr\Comparison
    {
        return $this->expr->like($field, $placeholder);
    }

    public function notLike(string $field, string $placeholder): Expr\Comparison
    {
        return $this->expr->notLike($field, $placeholder);
    }

    public function isMemberOf(string $field, string $placeholder): Expr\Comparison
    {
        return $this->expr->isMemberOf($field, $placeholder);
    }

    public function comparison(string $field, string $operator, string $placeholder): Expr\Comparison
    {
        $operator = $this->convertComparisonOperator($operator);
        if (!$operator) {
            throw new \RuntimeException("Unknown comparison operator: " . $operator);
        }

        return new Expr\Comparison(
            $field,
            $operator,
            $placeholder
        );
    }

    private function convertComparisonOperator($criteriaOperator): ?string
    {
        return self::$operatorMap[$criteriaOperator] ?? null;
    }

    public function in(string $field, string $placeholder): Expr\Func
    {
        return $this->expr->in($field, $placeholder);
    }

    public function notIn(string $field, string $placeholder): Expr\Func
    {
        return $this->expr->notIn($field, $placeholder);
    }

    public function isNull(string $field): string
    {
        return $this->expr->isNull($field);
    }

    public function isNotNull(string $field): string
    {
        return $this->expr->isNotNull($field);
    }

    public function not($expression): Expr\Func
    {
        return $this->expr->not($expression);
    }
}