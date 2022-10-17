<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Persister;

interface AggregateRootPersisterInterface
{
    public function persistNew(array $data): int;

    public function persistExisted($id, array $data): void;

    public function remove($id): void;
}