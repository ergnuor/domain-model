<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\RegistryInterface;

/**
 * @template TEntity
 * @extends  AbstractDomainRepository<TEntity>
 */
abstract class AbstractServiceDomainRepository extends AbstractDomainRepository
{
    public function __construct(
        string $className,
        RegistryInterface $registry
    ) {
        parent::__construct(
            $className,
            $registry->getDomainEntityManager(),
        );
    }
}
