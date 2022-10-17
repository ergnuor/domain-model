<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork\DomainEventDispatcher;
use Ergnuor\DomainModel\Event\DomainEventInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class EventManager
{
    private const DISPATCH_MOMENT_BEFORE_TRANSACTION = 'beforeTransaction';
    private const DISPATCH_MOMENT_BEFORE_COMMIT = 'beforeCommit';
    private const DISPATCH_MOMENT_AFTER_TRANSACTION = 'afterTransaction';

    private UnitOfWork $unitOfWork;
    private DomainEventDispatcher $domainEventDispatcher;
    /** @var DomainEventInterface[] */
    private array $events;

    private array $dispatchedEventOidsByMoment;

    public function __construct(
        UnitOfWork $unitOfWork,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->unitOfWork = $unitOfWork;
        $this->domainEventDispatcher = new DomainEventDispatcher($eventDispatcher);

        $this->events = [];
        $this->dispatchedEventOidsByMoment = [
            self::DISPATCH_MOMENT_BEFORE_TRANSACTION => [],
            self::DISPATCH_MOMENT_BEFORE_COMMIT => [],
            self::DISPATCH_MOMENT_AFTER_TRANSACTION => [],
        ];
    }

    public function dispatchBeforeTransactionEvents()
    {
        $this->doDispatchEvents(
            self::DISPATCH_MOMENT_BEFORE_TRANSACTION,
            function (DomainEventInterface $event) {
                $this->domainEventDispatcher->dispatchBeforeTransaction($event);
            }
        );
    }

    private function doDispatchEvents(string $dispatchMoment, $handlerCallback)
    {
        $iterationLimit = 100;
        $iterationCount = 0;

        $newEvents = $this->collectEvents($dispatchMoment);

        while (count($newEvents) > 0) {
            foreach ($newEvents as $eventOid => $event) {
                call_user_func_array($handlerCallback, [$event]);
                $this->dispatchedEventOidsByMoment[$dispatchMoment][$eventOid] = true;
            }

            $iterationCount++;

            if ($iterationCount > $iterationLimit) {
                throw new \RuntimeException("Event processing iteration limit of '{$iterationLimit}' iterations reached");
            }

            $newEvents = $this->collectEvents($dispatchMoment);
        }
    }

    /**
     * Собирает события в общий массив и возвращает еще не обработанные события для конкретного момента диспетчеризации
     *
     * @return array
     */
    private function collectEvents(string $dispatchMoment): array
    {
        $this->doCollectAggregateRootsEvents($this->unitOfWork->getScheduledAggregatesToCreate());
        $this->doCollectAggregateRootsEvents($this->unitOfWork->getScheduledAggregatesToUpdate());
        $this->doCollectAggregateRootsEvents($this->unitOfWork->getScheduledAggregatesToRemove());

        return array_diff_key(
            $this->events,
            $this->dispatchedEventOidsByMoment[$dispatchMoment],
        );
    }

    /**
     * @param DomainAggregateInterface[] $aggregateRoots
     * @return array
     */
    private function doCollectAggregateRootsEvents(array $aggregateRoots): array
    {
        $events = [];
        foreach ($aggregateRoots as $aggregateRoot) {
            $events = array_merge($events, $this->doCollectAggregateRootEvents($aggregateRoot));
        }

        return $events;
    }

    private function doCollectAggregateRootEvents(DomainAggregateInterface $aggregateRoot): array
    {
        $eventBag = $aggregateRoot->getEventBag();
        $events = $eventBag->getEvents();
        $eventBag->clear();

        foreach ($events as $event) {
            $eventOid = spl_object_id($event);

            if (isset($this->events[$eventOid])) {
                $eventType = get_debug_type($event);

                throw new \RuntimeException("Duplicate event instance of type '{$eventType}' found. You probably trying to reuse the same event object.");
            }

            $this->events[$eventOid] = $event;
        }

        return $events;
    }

    public function dispatchBeforeCommitEvents()
    {
        $this->doDispatchEvents(
            self::DISPATCH_MOMENT_BEFORE_COMMIT,
            function (DomainEventInterface $event) {
                $this->domainEventDispatcher->dispatchBeforeCommit($event);
            }
        );
    }

    public function dispatchAfterTransactionEvents()
    {
        $this->doDispatchEvents(
            self::DISPATCH_MOMENT_AFTER_TRANSACTION,
            function (DomainEventInterface $event) {
                $this->domainEventDispatcher->dispatchAfterTransaction($event);
            }
        );
    }
}