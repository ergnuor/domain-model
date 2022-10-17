<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;

class CallbackCustomExpressionMapper implements CustomExpressionMapperInterface
{
    /** @var callable */
    private $getExpression;

    public function __construct(callable $getExpression)
    {
        $this->getExpression = $getExpression;
    }

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
    ): mixed {
        return call_user_func_array($this->getExpression, [$comparison, $parameter, $placeholder]);
    }
}