<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Repository\DomainRepositoryInterface;
use Ergnuor\DomainModel\Mapping\ClassMetadataInterface;
use Ergnuor\Mapping\ClassMetadataFactoryInterface;

interface EntityManagerInterface
{
    public function persist($object): void;

    public function remove($object): void;

    public function flush(): void;

    public function getUnitOfWork(): UnitOfWorkInterface;

    public function getClassMetadata(string $className): ClassMetadataInterface;

    /** @return ClassMetadataFactoryInterface<ClassMetadataInterface> */
    public function getClassMetadataFactory(): ClassMetadataFactoryInterface;

    public function getRepository(string|DomainAggregateInterface $objectOrClassName): DomainRepositoryInterface;
}