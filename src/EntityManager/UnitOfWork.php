<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager;

use Ergnuor\DomainModel\Transaction\TransactionManagerInterface;
use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Entity\DomainEntityInterface;
use Ergnuor\DomainModel\Persister\AggregateRootPersisterInterface;
use Ergnuor\DomainModel\Persister\EntityPersisterInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\Events;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\LifecycleEvent;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\Event;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\EntityCollector;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\EventManager;
use Ergnuor\DomainModel\Mapping\ClassMetadata;
use Ergnuor\DomainModel\Serializer\Normalizer\DateTimeNormalizer;
use Ergnuor\DomainModel\Serializer\Normalizer\DomainEntityNormalizer;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;

class UnitOfWork implements UnitOfWorkInterface
{
    private ContainerInterface $persistersLocator;
    private Serializer $serializer;
    private EntityManager $domainEntityManager;
    private array $identityMap = [];

    /** @var DomainAggregateInterface[] */
    private array $aggregatesToCreate = [];
    /** @var DomainAggregateInterface[] */
    private array $aggregatesToUpdate = [];
    /** @var DomainAggregateInterface[] */
    private array $aggregatesToRemove = [];
    private TransactionManagerInterface $transactionManager;
    private bool $closed = false;
    private bool $commiting = false;
    private array $processedAggregateOids;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ContainerInterface $persistersLocator,
        Serializer $serializer,
        TransactionManagerInterface $transactionManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->persistersLocator = $persistersLocator;
        $this->serializer = $serializer;
        $this->transactionManager = $transactionManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setEntityManager(EntityManager $domainEntityManager)
    {
        $this->domainEntityManager = $domainEntityManager;
    }

    public function persist(DomainAggregateInterface $object): void
    {
        $classMetadata = $this->getClassMetadata(get_class($object));

        $oid = spl_object_id($object);
        if (!$classMetadata->hasEntityIdentifierValues($object)) {
            $this->aggregatesToCreate[$oid] = $object;
        } else {
            $this->aggregatesToUpdate[$oid] = $object;
        }

        if (isset($this->aggregatesToRemove[$oid])) {
            unset($this->aggregatesToRemove[$oid]);
        }

        if (isset($this->processedAggregateOids[$oid])) {
            unset($this->processedAggregateOids[$oid]);
        }
    }

    public function getClassMetadata(string $className): ClassMetadata
    {
        $classMetadata = $this->domainEntityManager->getClassMetadata($className);
        assert($classMetadata instanceof ClassMetadata);
        return $classMetadata;
    }

    public function remove(DomainAggregateInterface $object): void
    {
        $oid = spl_object_id($object);

        $this->aggregatesToRemove[$oid] = $object;

        if (isset($this->aggregatesToCreate[$oid])) {
            unset($this->aggregatesToCreate[$oid]);
        }

        if (isset($this->aggregatesToUpdate[$oid])) {
            unset($this->aggregatesToUpdate[$oid]);
        }

        if (isset($this->processedAggregateOids[$oid])) {
            unset($this->processedAggregateOids[$oid]);
        }
    }

    public function commit(): void
    {
        if ($this->closed) {
            throw new \RuntimeException('Can not perform commit on closed UnitOfWork');
        }

        if ($this->commiting) {
            return;
        }

        $this->commiting = true;

        $eventManager = new EventManager($this, $this->eventDispatcher);

        $entityCollector = new EntityCollector($this);

        $eventManager->dispatchBeforeTransactionEvents();

        $this->transactionManager->beginTransaction();

        $this->processedAggregateOids = [];
        try {
            do {
                $entityCollector->reset();
                $hasProcessedAggregate = false;
                $involvedPersisters = $this->getInvolvedAggregateRootPersisters();

                foreach ($this->aggregatesToCreate as $oid => $aggregate) {
                    if (isset($this->processedAggregateOids[$oid])) {
                        continue;
                    }

                    $aggregateClassName = get_class($aggregate);
                    $persister = $involvedPersisters[$aggregateClassName];
                    $idValues = $persister->persistNew(
                        $this->normalizeAggregateRootForPersister($aggregate)
                    );

                    $this->refreshCreatedEntity($idValues, $aggregate);

                    $entityCollector->collectEntities($aggregate);

                    $this->processedAggregateOids[$oid] = true;
                    $hasProcessedAggregate = true;
                }

                foreach ($this->aggregatesToUpdate as $oid => $aggregate) {
                    if (isset($this->processedAggregateOids[$oid])) {
                        continue;
                    }

                    $idValues = $this->getEntityIdentifierValues($aggregate);

                    $aggregateClassName = get_class($aggregate);
                    $persister = $involvedPersisters[$aggregateClassName];
                    $persister->persistExisted(
                        count($idValues) > 1 ? $idValues : $idValues[array_key_first($idValues)],
                        $this->normalizeAggregateRootForPersister($aggregate)
                    );

                    $entityCollector->collectEntities($aggregate);

//                    $this->refreshEntity($idValues, $aggregate);

                    $this->processedAggregateOids[$oid] = true;
                    $hasProcessedAggregate = true;
                }

                foreach ($this->aggregatesToRemove as $oid => $aggregate) {
                    if (isset($this->processedAggregateOids[$oid])) {
                        continue;
                    }

                    $idValues = $this->getEntityIdentifierValues($aggregate);

                    $aggregateClassName = get_class($aggregate);
                    $persister = $involvedPersisters[$aggregateClassName];
                    $persister->remove(
                        count($idValues) > 1 ? $idValues : $idValues[array_key_first($idValues)]
                    );

                    $this->processedAggregateOids[$oid] = true;
                    $hasProcessedAggregate = true;
                }

                $this->commitEntities($entityCollector);

                $eventManager->dispatchBeforeCommitEvents();

                $this->dispatchEntityManagerLifecycleEvent(Events::EVENT_BEFORE_COMMIT_FLUSH);
            } while ($hasProcessedAggregate);

            $this->transactionManager->commitTransaction();

            $eventManager->dispatchAfterTransactionEvents();
            $this->dispatchEntityManagerLifecycleEvent(Events::EVENT_AFTER_TRANSACTION_FLUSH);

            $this->dispatchEntityManagerEvent(Events::EVENT_POST_FLUSH);
        } catch (\Throwable $e) {
            $this->close();
            $this->transactionManager->rollbackTransaction();

            throw $e;
        } finally {
            $this->clearAfterFlush();
            $this->commiting = false;
        }
    }

    private function dispatchEntityManagerLifecycleEvent(string $eventName)
    {
        foreach ($this->aggregatesToCreate as $aggregate) {
            $this->eventDispatcher->dispatch(new LifecycleEvent($this, $aggregate), $eventName);
        }

        foreach ($this->aggregatesToUpdate as $aggregate) {
            $this->eventDispatcher->dispatch(new LifecycleEvent($this, $aggregate), $eventName);
        }

        foreach ($this->aggregatesToRemove as $aggregate) {
            $this->eventDispatcher->dispatch(new LifecycleEvent($this, $aggregate), $eventName);
        }
    }

    private function dispatchEntityManagerEvent(string $eventName)
    {
        $this->eventDispatcher->dispatch(new Event($this), $eventName);
    }

    /**
     * @return AggregateRootPersisterInterface[]
     */
    private function getInvolvedAggregateRootPersisters(): array
    {
        $involvedPersisters = [];

        foreach ($this->aggregatesToCreate as $oid => $entity) {
            $this->addInvolvedAggregateRootPersister($entity, $involvedPersisters);
        }

        foreach ($this->aggregatesToUpdate as $oid => $entity) {
            $this->addInvolvedAggregateRootPersister($entity, $involvedPersisters);
        }

        foreach ($this->aggregatesToRemove as $oid => $entity) {
            $this->addInvolvedAggregateRootPersister($entity, $involvedPersisters);
        }

        return $involvedPersisters;
    }

    private function addInvolvedAggregateRootPersister(DomainAggregateInterface $aggregate, &$involvedPersisters): void
    {
        $aggregateClassName = get_class($aggregate);
        if (isset($involvedPersisters[$aggregateClassName])) {
            return;
        }

        $involvedPersisters[$aggregateClassName] = $this->getAggregateRootPersister($aggregateClassName);
    }

    private function getAggregateRootPersister(string $className): AggregateRootPersisterInterface
    {
        $classMetadata = $this->getClassMetadata($className);
        return $this->persistersLocator->get($classMetadata->getPersisterClass());
    }

    private function getEntityPersister(string $className): EntityPersisterInterface
    {
        $classMetadata = $this->getClassMetadata($className);
        return $this->persistersLocator->get($classMetadata->getPersisterClass());
    }

    private function normalizeAggregateRootForPersister(DomainAggregateInterface $aggregateRoot): array
    {
        return $this->serializer->normalize(
            $aggregateRoot,
            null,
            [
//                DateTimeNormalizer::NORMALIZE_AS_OBJECT => true,
                DomainEntityNormalizer::NORMALIZE_FOR_PERSISTER => true,
            ]
        );
    }

    private function normalizeEntityForPersister(DomainEntityInterface $entity): array
    {
        return $this->serializer->normalize(
            $entity,
            null,
            [
//                DateTimeNormalizer::NORMALIZE_AS_OBJECT => true,
                DomainEntityNormalizer::NORMALIZE_FOR_PERSISTER => true,
            ]
        );
    }

    private function refreshCreatedEntity(
        int $idValues,
        DomainEntityInterface $entity
    ) {
        if (empty($idValues)) {
            throw new \RuntimeException("Persister persistNew method should return identifier");
        }

        $entityClassName = get_class($entity);
        $classMetadata = $this->getClassMetadata($entityClassName);
        $identifiers = $classMetadata->getIdentifiers();

        if (is_scalar($idValues)) {
            $scalarIdValue = $idValues;

            $idValues = [
                $identifiers[array_key_first($identifiers)] => $scalarIdValue,
            ];
        } elseif (is_array($idValues)) {
            $notSpecifiedIdentifiers = array_diff_key(
                $idValues,
                array_flip($identifiers),
            );

            if (!empty($notSpecifiedIdentifiers)) {
                $notSpecifiedIdentifiersText = implode(', ', array_keys($notSpecifiedIdentifiers));
                throw new \RuntimeException("Not all identifiers specified for class '{$entityClassName}'. Missing identifiers: '{$notSpecifiedIdentifiersText}'");
            }
        } else {
            $identifierType = get_debug_type($idValues);
            throw new \RuntimeException("Unsupported identifier type '{$identifierType}'");
        }

//                    $this->refreshEntity($idValues, $entity);

        $context = [
            UnitOfWorkInterface::CONTEXT_OBJECT_TO_REFRESH => $entity,
        ];

        $this->createEntity($entityClassName, $idValues, $context);
    }

    public function createEntity(string $className, array $data, array $context = [])
    {
        $classMetadata = $this->getClassMetadata($className);

        $identityKey = $classMetadata->getFlattenedIdentifierFromRawData($data);

        $this->identityMap[$className] = $this->identityMap[$className] ?? [];

        if (
            !isset($this->identityMap[$className][$identityKey]) ||
            isset($context[UnitOfWork::CONTEXT_OBJECT_TO_REFRESH])
        ) {

            $staticFactoryMethod = $classMetadata->getStaticFactoryMethodName();
            if ($staticFactoryMethod !== null) {
                throw new \RuntimeException('Entity factory method is not supported yet');
//                dd(call_user_func_array([$classMetadata->getClassName(), $staticFactoryMethod], [$data]));
            }

            $this->identityMap[$className][$identityKey] = $this->serializer->denormalize(
                $data,
                $className,
                null,
                $context
            );
        }

        return $this->identityMap[$className][$identityKey];
    }

//    private function refreshEntity($idValues, $entity)
//    {
//        $repository = $this->domainEntityManager->getRepository($entity);
//        $repository->refresh($idValues, $entity);
//    }

    /**
     * @param DomainAggregateInterface $entity
     * @return array
     *
     * todo нужен ли метод?
     */
    private function getEntityIdentifierValues(DomainAggregateInterface $entity): array
    {
        $classMetadata = $this->getClassMetadata(get_class($entity));
        return $classMetadata->getEntityIdentifierValues($entity);
    }

    private function commitEntities(EntityCollector $entityCollector)
    {
//        dd([
//            $entityCollector->getEntitiesToCreate(),
//            $entityCollector->getEntitiesToUpdate(),
//        ]);
        foreach ($entityCollector->getEntitiesToCreate() as $aggregateRootClassName => $aggregateRoots) {
            foreach ($aggregateRoots as $aggregateRootId => $entitiesByClassName) {
                foreach ($entitiesByClassName as $entityClassName => $entities) {
                    foreach ($entities as $entityData) {
                        $parentEntityId = null;
                        if ($entityData['parentEntity'] !== null) {
                            $parentEntityClassName = get_class($entityData['parentEntity']);
                            $parentEntityClassMetadata = $this->getClassMetadata($parentEntityClassName);
                            $parentEntityId = $parentEntityClassMetadata->getEntityIdentifierValue($entityData['parentEntity']);
                        }

                        $entityClassName = get_class($entityData['entity']);
                        $persister = $this->getEntityPersister($entityClassName);
                        $newEntityId = $persister->persistNew(
                            $aggregateRootId,
                            $parentEntityId,
                            $this->normalizeEntityForPersister($entityData['entity'])
                        );
                        $this->refreshCreatedEntity($newEntityId, $entityData['entity']);
                    }
                }
            }
        }

        foreach ($entityCollector->getEntitiesToUpdate() as $aggregateRootClassName => $aggregateRoots) {
            foreach ($aggregateRoots as $aggregateRootId => $entitiesByClassName) {
                foreach ($entitiesByClassName as $entityClassName => $entities) {
                    foreach ($entities as $entityData) {
                        $parentEntityId = null;
                        if ($entityData['parentEntity'] !== null) {
                            $parentEntityClassName = get_class($entityData['parentEntity']);
                            $parentEntityClassMetadata = $this->getClassMetadata($parentEntityClassName);
                            $parentEntityId = $parentEntityClassMetadata->getEntityIdentifierValue($entityData['parentEntity']);
                        }

                        $entityClassName = get_class($entityData['entity']);
                        $entityClassMetadata = $this->getClassMetadata($entityClassName);
                        $entityId = $entityClassMetadata->getEntityIdentifierValue($entityData['entity']);

                        $persister = $this->getEntityPersister($entityClassName);
                        $persister->persistExisted(
                            $aggregateRootId,
                            $parentEntityId,
                            $entityId,
                            $this->normalizeEntityForPersister($entityData['entity'])
                        );
                    }
                }
            }
        }

        foreach ($entityCollector->getExistedEntities() as $entityClassName => $data) {
            $persister = $this->getEntityPersister($entityClassName);
            $persister->remove($data['aggregateRootIds'], $data['parentEntityIds'], $data['entityIds']);
        }
    }

    private function close()
    {
        $this->closed = true;
    }

    private function clearAfterFlush()
    {
        $this->aggregatesToCreate = [];
        $this->aggregatesToUpdate = [];
        $this->aggregatesToRemove = [];
    }

    /**
     * @return DomainAggregateInterface[]
     */
    public function getScheduledAggregatesToCreate(): array
    {
        return $this->aggregatesToCreate;
    }

    /**
     * @return DomainAggregateInterface[]
     */
    public function getScheduledAggregatesToUpdate(): array
    {
        return $this->aggregatesToUpdate;
    }

    /**
     * @return DomainAggregateInterface[]
     */
    public function getScheduledAggregatesToRemove(): array
    {
        return $this->aggregatesToRemove;
    }
}