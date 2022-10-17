<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use Ergnuor\DomainModel\DataAccess\ExpressionMapper\ExpressionMapperInterface;

class NegationExpression implements ExpressionInterface
{
    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }
    /**
     * {@inheritDoc}
     */
    public function visit(ExpressionMapperInterface $visitor): mixed
    {
        return $visitor->walkNegationExpression($this);
    }
}
