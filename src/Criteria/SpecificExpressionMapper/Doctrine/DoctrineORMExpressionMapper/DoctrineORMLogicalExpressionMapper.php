<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper;

use Doctrine\ORM\Query\Expr;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\LogicalExpressionMapperInterface;

class DoctrineORMLogicalExpressionMapper implements LogicalExpressionMapperInterface
{
    private Expr $expr;

    public function __construct()
    {
        $this->expr = new Expr();
    }

    public function andX(...$x): Expr\Andx
    {
        return $this->expr->andX(...$x);
    }

    public function not($expression): Expr\Func
    {
        return $this->expr->not($expression);
    }

    public function orX(...$x): Expr\Orx
    {
        return $this->expr->orX(...$x);
    }
}