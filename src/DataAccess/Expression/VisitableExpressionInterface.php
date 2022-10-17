<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use Ergnuor\DomainModel\DataAccess\ExpressionMapper\ExpressionMapperInterface;

interface VisitableExpressionInterface
{
    /**
     * @return mixed
     */
    public function visit(ExpressionMapperInterface $visitor): mixed;
}
