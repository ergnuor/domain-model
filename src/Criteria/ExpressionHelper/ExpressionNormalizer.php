<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionHelper;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;

class ExpressionNormalizer
{
    public static function normalize(array|ExpressionInterface|null $expression): ?ExpressionInterface
    {
        if ($expression instanceof ExpressionInterface) {
            return $expression;
        }

        if (empty($expression)) {
            return null;
        }

        $fromArrayCriteriaBuilder = new FromArrayExpressionBuilder();
        return $fromArrayCriteriaBuilder->build($expression);
    }
}