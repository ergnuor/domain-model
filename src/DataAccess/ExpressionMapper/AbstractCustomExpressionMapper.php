<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;

abstract class AbstractCustomExpressionMapper implements CustomExpressionMapperInterface
{
    protected function getBooleanParameterRealValue(ComparisonExpression $comparison, Parameter $parameter): bool
    {
        return (bool)$parameter->getValue() xor $comparison->isInverted();
    }
}