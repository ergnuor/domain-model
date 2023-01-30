<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Specification;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\ExpressionBuilder as expr;
use Ergnuor\DomainModel\Entity\DomainAggregateInterface;

class NegationSpecification extends AbstractSpecification
{
    private SpecificationInterface $specification;

    public function __construct(SpecificationInterface $specification)
    {
        $this->specification = $specification;
    }

    public function isSatisfiedBy(DomainAggregateInterface $aggregate): bool
    {
        return !$this->specification->isSatisfiedBy($aggregate);
    }

    public function toExpression(): ExpressionInterface
    {
        return expr::not($this->specification->toExpression());
    }
}