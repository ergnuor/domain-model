<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Doctrine\ORM\Query\Expr;

abstract class AbstractDoctrineCustomExpressionMapper extends AbstractCustomExpressionMapper
{
    protected Expr $expr;

    public function __construct()
    {
        $this->expr = new Expr();
    }
}
