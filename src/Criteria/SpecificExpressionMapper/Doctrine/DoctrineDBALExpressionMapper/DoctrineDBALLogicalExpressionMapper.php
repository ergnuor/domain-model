<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineDBALExpressionMapper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\LogicalExpressionMapperInterface;

class DoctrineDBALLogicalExpressionMapper implements LogicalExpressionMapperInterface
{
    private ExpressionBuilder $expr;

    public function __construct(Connection $connection)
    {
        $this->expr = $connection->createExpressionBuilder();
    }

    public function andX(...$x): CompositeExpression
    {
        return $this->expr->and(...$x);
    }

    public function not($expression): string
    {
        return 'NOT(' . $expression . ')';
    }

    public function orX(...$x): CompositeExpression
    {
        return $this->expr->or(...$x);
    }
}