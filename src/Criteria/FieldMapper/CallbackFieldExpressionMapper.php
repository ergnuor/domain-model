<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\FieldMapper;

use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionContext;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\FieldMapResult;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\Identifiers;

class CallbackFieldExpressionMapper implements FieldExpressionMapperInterface
{
    /** @var callable */
    private $getExpression;

    public function __construct(callable $getExpression)
    {
        $this->getExpression = $getExpression;
    }

    public function mapExpression(
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): ?FieldMapResult {
        return call_user_func_array($this->getExpression, [$expressionContext, $identifiers]);
    }
}