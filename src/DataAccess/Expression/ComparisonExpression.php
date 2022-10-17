<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use Ergnuor\DomainModel\DataAccess\ExpressionMapper\ExpressionMapperInterface;

class ComparisonExpression implements ExpressionInterface
{
    public const EQ = '=';
    public const NEQ = '<>';
    public const LT = '<';
    public const LTE = '<=';
    public const GT = '>';
    public const GTE = '>=';
    public const IS = '='; // no difference with EQ
    public const IN = 'IN';
    public const NIN = 'NIN';
    public const CONTAINS = 'CONTAINS';
    public const NCONTAINS = 'NCONTAINS';
    public const MEMBER_OF = 'MEMBER_OF';
    public const STARTS_WITH = 'STARTS_WITH';
    public const NSTARTS_WITH = 'NSTARTS_WITH';
    public const ENDS_WITH = 'ENDS_WITH';
    public const NENDS_WITH = 'NENDS_WITH';

    private string $field;

    private string $op;

    private Value $value;

    public function __construct(string $field, string $operator, $value)
    {
        if (!($value instanceof Value)) {
            $value = new Value($value);
        }

        $this->field = $field;
        $this->op = $operator;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): Value
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->op;
    }

    public function isInverted(): bool
    {
        return in_array(
            $this->getOperator(),
            [
                self::NEQ,
                self::NIN,
                self::NCONTAINS,
                self::NSTARTS_WITH,
                self::NENDS_WITH,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visit(ExpressionMapperInterface $visitor): mixed
    {
        return $visitor->walkComparisonExpression($this);
    }
}
