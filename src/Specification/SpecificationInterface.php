<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Specification;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Entity\DomainAggregateInterface;

interface SpecificationInterface
{
    public function isSatisfiedBy(DomainAggregateInterface $aggregate): bool;

    public function toExpression(): ExpressionInterface;
}