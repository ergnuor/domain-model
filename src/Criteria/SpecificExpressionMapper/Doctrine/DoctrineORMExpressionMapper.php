<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine;

use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\CommonParameterMapper\DoctrineParameterMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper\DoctrineORMBasicExpressionMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper\DoctrineORMLogicalExpressionMapper;
use Psr\Container\ContainerInterface;

class DoctrineORMExpressionMapper extends ExpressionMapper
{
    public function __construct(ContainerInterface $expressionMapperContainer = null)
    {
        parent::__construct(
            new DoctrineParameterMapper(),
            new DoctrineORMBasicExpressionMapper(),
            new DoctrineORMLogicalExpressionMapper(),
            $expressionMapperContainer,
        );
    }
}