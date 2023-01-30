<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine;

use Doctrine\DBAL\Connection;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\CommonParameterMapper\DoctrineParameterMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineDBALExpressionMapper\DoctrineDBALBasicExpressionMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineDBALExpressionMapper\DoctrineDBALLogicalExpressionMapper;
use Psr\Container\ContainerInterface;

class DoctrineDBALExpressionMapper extends ExpressionMapper
{
    public function __construct(Connection $connection, ContainerInterface $expressionMapperContainer = null)
    {
        parent::__construct(
            new DoctrineParameterMapper(),
            new DoctrineDBALBasicExpressionMapper($connection),
            new DoctrineDBALLogicalExpressionMapper($connection),
            $expressionMapperContainer,
        );
    }
}