<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;

interface CustomExpressionMapperInterface
{
    /**
     * @param ComparisonExpression $comparison
     * @param Parameter $parameter
     * @param string $placeholder
     * @return mixed|null
     */
    public function getExpression(
        ComparisonExpression $comparison,
        Parameter $parameter,
        string $placeholder,
        ExpressionMapperInterface $expressionMapper
    ): mixed;
}