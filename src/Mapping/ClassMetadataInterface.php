<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Entity\DomainEntityInterface;

interface ClassMetadataInterface
{
    public function getIdentifiers(): array;

    public function getFlattenedIdentifierFromRawData(array $data): string;

    public function getEntityIdentifierValues(DomainAggregateInterface|DomainEntityInterface $entity): array;

    public function getStaticFactoryMethodName(): ?string;

    public function getClassName(): string;

    public function getFieldNames(): array;

    public function getRepositoryClass(): string;

    public function getPersisterClass(): string;

    public function isAggregateRoot(): bool;
}