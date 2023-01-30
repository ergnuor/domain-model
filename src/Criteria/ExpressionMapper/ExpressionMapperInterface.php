<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Ergnuor\DomainModel\Criteria\Expression\CompositeExpression;
use Ergnuor\DomainModel\Criteria\Expression\NegationExpression;
use Ergnuor\DomainModel\Criteria\Expression\Value;
use Ergnuor\DomainModel\Criteria\Expression\VisitableExpressionInterface;
use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\Parameter;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

interface ExpressionMapperInterface
{
    /**
     * Converts a comparison expression into the target query language output.
     */
    public function walkNegationExpression(NegationExpression $negation): mixed;

    /**
     * Converts a comparison expression into the target query language output.
     */
    public function walkExpression(Expression $expression): mixed;

    /**
     * Converts a value expression into the target query language part.
     */
    public function walkValue(Value $value): mixed;

    /**
     * Converts a composite expression into the target query language output.
     */
    public function walkCompositeExpression(CompositeExpression $compositeExpression): mixed;

    /**
     * @return mixed
     */
    public function dispatch(VisitableExpressionInterface $expr): mixed;

    public function getMappedParameters(): mixed;

    public function addField(
        string $fieldName,
        ?ValueSourceInterface $valueSource,
        null|string|FieldExpressionMapperInterface $fieldExpressionMapper = null
    ): void;
}
