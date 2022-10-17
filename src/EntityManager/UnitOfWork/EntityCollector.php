<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Entity\DomainEntityInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork;

/**
 * @internal
 */
class EntityCollector
{
    private const ENTITIES_TO_CREATE = 'entitiesToCreate';
    private const ENTITIES_TO_UPDATE = 'entitiesToUpdate';

    private UnitOfWork $unitOfWork;

    private array $collectedEntities;
    private array $existedEntities;

    public function __construct(
        UnitOfWork $unitOfWork
    ) {
        $this->unitOfWork = $unitOfWork;

        $this->reset();
    }

    public function reset(): void
    {
        $this->collectedEntities = [
            self::ENTITIES_TO_CREATE => [],
            self::ENTITIES_TO_UPDATE => [],
        ];

        $this->existedEntities = [];
    }

    public function collectEntities(DomainAggregateInterface $aggregateRoot)
    {
        $this->doCollectEntities($aggregateRoot);
    }

    private function doCollectEntities(
        DomainAggregateInterface $aggregateRoot,
        ?DomainEntityInterface $parentEntity = null
    ) {
        $aggregateRootClassName = get_class($aggregateRoot);
        $aggregateRootClassMetadata = $this->unitOfWork->getClassMetadata($aggregateRootClassName);
        $aggregateRootId = $aggregateRootClassMetadata->getEntityIdentifierValue($aggregateRoot);

        if ($parentEntity !== null) {
            $parentEntityClassName = get_class($parentEntity);
            $containerEntityClassMetadata = $this->unitOfWork->getClassMetadata($parentEntityClassName);

            $containerEntity = $parentEntity;
        } else {
            $containerEntityClassMetadata = $aggregateRootClassMetadata;
            $containerEntity = $aggregateRoot;
        }

        $entityFieldNames = $containerEntityClassMetadata->getEntityFieldNames();

        foreach ($entityFieldNames as $entityFieldName) {
            $entityClassName = $containerEntityClassMetadata->getEntityClassName($entityFieldName);
            $entityClassMetadata = $this->unitOfWork->getClassMetadata($entityClassName);
            $isEntityCollection = $containerEntityClassMetadata->isEntityCollection($entityFieldName);
            $entities = $containerEntityClassMetadata->getFieldValue($containerEntity, $entityFieldName);

            if (!$isEntityCollection) {
                if (!($entities instanceof DomainEntityInterface)) {
                    continue;
                }
                $entities = [$entities];
            }

            $this->existedEntities[$entityClassName] = $this->existedEntities[$entityClassName] ?? [
                    'aggregateRootIds' => [],
                    'parentEntityIds' => [],
                    'entityIds' => [],
                ];
            $this->existedEntities[$entityClassName]['aggregateRootIds'][$aggregateRootId] = $aggregateRootId;

            foreach ($entities as $entity) {
                if (get_class($entity) != $entityClassName) {
                    throw new \RuntimeException(
                        sprintf(
                            "Collected entity is a '%s' class instance, but '%s' class expected. ",
                            get_class($entity),
                            $entityClassName
                        )
                    );
                }

                if (!$entityClassMetadata->hasEntityIdentifierValues($entity)) {
                    $entitiesGroup = self::ENTITIES_TO_CREATE;
                } else {
                    $entitiesGroup = self::ENTITIES_TO_UPDATE;
                }

                $this->collectedEntities[$entitiesGroup][$aggregateRootClassName] = $this->collectedEntities[$entitiesGroup][$aggregateRootClassName] ?? [];
                $this->collectedEntities[$entitiesGroup][$aggregateRootClassName][$aggregateRootId] = $this->collectedEntities[$entitiesGroup][$aggregateRootClassName][$aggregateRootId] ?? [];
                $this->collectedEntities[$entitiesGroup][$aggregateRootClassName][$aggregateRootId][$entityClassName]
                    = $this->collectedEntities[$entitiesGroup][$aggregateRootClassName][$aggregateRootId][$entityClassName] ?? [];

                $this->collectedEntities[$entitiesGroup][$aggregateRootClassName][$aggregateRootId][$entityClassName][] = [
                    'entity' => $entity,
                    'parentEntity' => $parentEntity,
                ];

                $this->doCollectEntities($aggregateRoot, $entity);
            }
        }
    }

    public function getEntitiesToCreate(): array
    {
        return $this->collectedEntities[self::ENTITIES_TO_CREATE];
    }

    public function getEntitiesToUpdate(): array
    {
        return $this->collectedEntities[self::ENTITIES_TO_UPDATE];
    }

    public function getExistedEntities(): array
    {
        foreach ($this->collectedEntities as $entityGroup => $aggregateRootClassNames) {
            foreach ($aggregateRootClassNames as $aggregateRootClassName => $aggregateRoots) {
                foreach ($aggregateRoots as $aggregateRootId => $entitiesByClassName) {
                    foreach ($entitiesByClassName as $entityClassName => $entities) {
                        foreach ($entities as $entityData) {
                            $entity = $entityData['entity'];

                            $entityClassMetadata = $this->unitOfWork->getClassMetadata($entityClassName);
                            $entityId = $entityClassMetadata->getEntityIdentifierValue($entity);
                            $this->existedEntities[$entityClassName]['entityIds'][$entityId] = $entityId;

                            $parentEntity = $entityData['parentEntity'];

                            if ($parentEntity !== null) {
                                $parentEntityClassMetadata = $this->unitOfWork->getClassMetadata(get_class($parentEntity));
                                $parentEntityId = $parentEntityClassMetadata->getEntityIdentifierValue($entity);
                                $this->existedEntities[$entityClassName]['parentEntityIds'][$parentEntityId] = $parentEntityId;
                            }
                        }
                    }
                }
            }
        }

        return $this->existedEntities;
    }
}