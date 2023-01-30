<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\RegistryInterface;

abstract class AbstractDataGetterServiceDomainRepository extends AbstractDataGetterDomainRepository
{
    public function __construct(string $className, RegistryInterface $registry)
    {
        parent::__construct(
            $className,
            $registry->getDomainEntityManager(),
            $registry->getDomainEntitySerializer(),
        );
    }
}
