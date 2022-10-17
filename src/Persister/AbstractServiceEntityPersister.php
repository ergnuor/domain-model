<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Persister;

use Ergnuor\DomainModel\RegistryInterface;

abstract class AbstractServiceEntityPersister extends AbstractEntityPersister
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct(
            $registry->getDomainEntityManager()
        );
    }
}