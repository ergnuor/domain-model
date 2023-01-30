<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Expression;

use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapperInterface;

class Expression implements ExpressionInterface
{
    public const EQ = '=';
    public const NOT_EQ = '!=';

    public const LT = '<';
    public const LTE = '<=';
    public const GT = '>';
    public const GTE = '>=';

    public const IN = 'IN';
    public const NOT_IN = 'NOT_IN';

    public const LIKE = 'LIKE';
    public const NOT_LIKE = 'NOT_LIKE';

    private string $fieldName;

    private string $op;

    private Value $value;

    public function __construct(string $fieldName, string $operator, mixed $value)
    {
        $this->fieldName = $fieldName;
        $this->op = $operator;
        $this->setValue($value);
    }

    private function setValue($value): void
    {
        if (!($value instanceof Value)) {
            $value = new Value($value);
        }

        $this->value = $value;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getValue(): Value
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->op;
    }

    public function withValue($value): Expression
    {
        return new Expression($this->getFieldName(), $this->getOperator(), $value);
    }

    /**
     * {@inheritDoc}
     */
    public function visit(ExpressionMapperInterface $visitor): mixed
    {
        return $visitor->walkExpression($this);
    }
}
