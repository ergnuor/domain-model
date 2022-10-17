<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;

interface UnitOfWorkInterface
{
    const CONTEXT_OBJECT_TO_REFRESH = 'objectToRefresh';

    public function createEntity(string $className, array $data, array $context = []);

    public function persist(DomainAggregateInterface $object): void;

    public function remove(DomainAggregateInterface $object): void;

    public function commit(): void;
}