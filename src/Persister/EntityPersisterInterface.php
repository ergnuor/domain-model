<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Persister;

interface EntityPersisterInterface
{
    public function persistNew($aggregateRootId, $parentEntityId, array $data): int;

    public function persistExisted($aggregateRootId, $parentEntityId, $id, array $data): void;

    public function remove(array $existedAggregateRootIds, array $parentEntityIds, array $existedEntityIds): void;
}