<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionBuilder;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;

trait ExpressionTrait
{
    protected function normalizeExpression(array|ExpressionInterface|null $expression): ?ExpressionInterface
    {
        if ($expression instanceof ExpressionInterface) {
            return $expression;
        }

        if (empty($expression)) {
            return null;
        }

        $fromArrayCriteriaBuilder = new FromArray();
        return $fromArrayCriteriaBuilder->create($expression);
    }
}