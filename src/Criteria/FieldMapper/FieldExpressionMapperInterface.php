<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\FieldMapper;

use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionContext;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\FieldMapResult;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\Identifiers;

interface FieldExpressionMapperInterface
{
    public function mapExpression(ExpressionContext $expressionContext, Identifiers $identifiers): ?FieldMapResult;
}