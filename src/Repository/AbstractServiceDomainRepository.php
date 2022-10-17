<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\RegistryInterface;

abstract class AbstractServiceDomainRepository extends AbstractDomainRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct(
            $registry->getDomainEntityManager(),
            $registry->getDomainEntitySerializer()
        );
    }
}
