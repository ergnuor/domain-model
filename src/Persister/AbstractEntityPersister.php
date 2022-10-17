<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Persister;

use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;

abstract class AbstractEntityPersister implements EntityPersisterInterface
{
    protected EntityManagerInterface $domainEntityManager;

    public function __construct(EntityManagerInterface $domainEntityManager)
    {
        $this->domainEntityManager = $domainEntityManager;
    }
}