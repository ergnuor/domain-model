<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;
use Ergnuor\DomainModel\DataAccess\Expression\CompositeExpression;
use Ergnuor\DomainModel\DataAccess\Expression\NegationExpression;
use Ergnuor\DomainModel\DataAccess\Expression\Value;
use Ergnuor\DomainModel\DataAccess\Expression\VisitableExpressionInterface;
use RuntimeException;
use function get_class;

/**
 * An Expression visitor walks a graph of expressions and turns them into a
 * query for the underlying implementation.
 */
abstract class AbstractCommonExpressionMapper implements ExpressionMapperInterface
{
    /**
     * Converts a comparison expression into the target query language output.
     *
     * @return mixed
     */
    abstract public function walkNegationExpression(NegationExpression $negation): mixed;

    /**
     * Converts a comparison expression into the target query language output.
     *
     * @return mixed
     */
    abstract public function walkComparisonExpression(ComparisonExpression $comparison): mixed;

    /**
     * Converts a value expression into the target query language part.
     *
     * @return mixed
     */
    abstract public function walkValue(Value $value): mixed;

    /**
     * Converts a composite expression into the target query language output.
     *
     * @return mixed
     */
    abstract public function walkCompositeExpression(CompositeExpression $compositeExpression): mixed;

    /**
     * Dispatches walking an expression to the appropriate handler.
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function dispatch(VisitableExpressionInterface $expr): mixed
    {
        switch (true) {
            case $expr instanceof ComparisonExpression:
                return $this->walkComparisonExpression($expr);
            case $expr instanceof Value:
                return $this->walkValue($expr);
            case $expr instanceof CompositeExpression:
                return $this->walkCompositeExpression($expr);
            case $expr instanceof NegationExpression:
                return $this->walkNegationExpression($expr);
            default:
                throw new RuntimeException('Unknown Expression ' . get_class($expr));
        }
    }
}
