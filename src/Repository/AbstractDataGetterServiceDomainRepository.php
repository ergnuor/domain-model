<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\Criteria\ExpressionMapper\ExpressionMapperInterface;
use Ergnuor\Criteria\OrderMapper\OrderMapperInterface;
use Ergnuor\DomainModel\RegistryInterface;

/**
 * @template TEntity
 * @template TExpression
 * @template TParameters
 * @template TOrder
 * @extends  AbstractDataGetterDomainRepository<TEntity, TExpression, TParameters, TOrder>
 */
abstract class AbstractDataGetterServiceDomainRepository extends AbstractDataGetterDomainRepository
{
    public function __construct(
        string $className,
        ExpressionMapperInterface $expressionMapper,
        OrderMapperInterface $orderMapper,
        RegistryInterface $registry
    ) {
        parent::__construct(
            $className,
            $registry->getDomainEntityManager(),
            $expressionMapper,
            $orderMapper,
            $registry->getConfigBuilder()
        );
    }
}
