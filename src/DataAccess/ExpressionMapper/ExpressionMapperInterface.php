<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;
use Ergnuor\DomainModel\DataAccess\Expression\CompositeExpression;
use Ergnuor\DomainModel\DataAccess\Expression\NegationExpression;
use Ergnuor\DomainModel\DataAccess\Expression\Value;
use Ergnuor\DomainModel\DataAccess\Expression\VisitableExpressionInterface;

interface ExpressionMapperInterface
{
    /**
     * @return mixed
     */
    public function walkNegationExpression(NegationExpression $negation): mixed;

    /**
     * @return mixed
     */
    public function walkComparisonExpression(ComparisonExpression $comparison): mixed;

    /**
     * @return mixed
     */
    public function walkValue(Value $value): mixed;

    /**
     * @return mixed
     */
    public function walkCompositeExpression(CompositeExpression $compositeExpression): mixed;

    /**
     * @return mixed
     */
    public function dispatch(VisitableExpressionInterface $expr): mixed;

    public function getMappedParameters();

    public function addParameter(Parameter $parameter): void;

    /**
     * @param ComparisonExpression $comparison
     * @param Parameter $parameter
     * @param string $field
     * @param string $placeholder
     * @return mixed
     */
    public function getBasicExpression(
        ComparisonExpression $comparison,
        Parameter $parameter,
        string $field,
        string $placeholder
    ): mixed;
}
