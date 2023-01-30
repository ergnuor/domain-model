<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\SpecificRepository\Doctrine;

use Ergnuor\DomainModel\RegistryInterface;

abstract class AbstractDoctrineQueryBuilderServiceDomainRepository extends AbstractDoctrineQueryBuilderDomainRepository
{
    public function __construct(string $className, RegistryInterface $registry)
    {
        parent::__construct(
            $className,
            $registry->getDomainEntityManager(),
            $registry->getDomainEntitySerializer(),
            $registry->getConfigBuilder(),
            $registry->getExpressionMapperContainer()
        );
    }
}
