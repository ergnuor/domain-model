<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Expression;

use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapperInterface;

interface VisitableExpressionInterface
{
    /**
     * @return mixed
     */
    public function visit(ExpressionMapperInterface $visitor): mixed;
}
