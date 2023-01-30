<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Ergnuor\DomainModel\Criteria\Expression\CompositeExpression;
use Ergnuor\DomainModel\Criteria\Expression\NegationExpression;
use Ergnuor\DomainModel\Criteria\Expression\Value;
use Ergnuor\DomainModel\Criteria\Expression\VisitableExpressionInterface;
use RuntimeException;
use function get_class;

/**
 * An Expression visitor walks a graph of expressions and turns them into a
 * query for the underlying implementation.
 */
abstract class AbstractExpressionMapper implements ExpressionMapperInterface
{
    /**
     * Dispatches walking an expression to the appropriate handler.
     */
    public function dispatch(VisitableExpressionInterface $expr): mixed
    {
        return match (true) {
            $expr instanceof CompositeExpression => $this->walkCompositeExpression($expr),
            $expr instanceof Expression => $this->walkExpression($expr),
            $expr instanceof NegationExpression => $this->walkNegationExpression($expr),
            $expr instanceof Value => $this->walkValue($expr),

            default => throw new RuntimeException('Unknown Expression ' . get_class($expr)),
        };
    }
}
