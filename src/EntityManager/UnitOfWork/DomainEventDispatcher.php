<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

use Ergnuor\DomainModel\Event\DomainEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DomainEventDispatcher
{
    private const BEFORE_TRANSACTION_EVENT_PREFIX = 'ergnuor.domain_model.before_transaction.';
    private const BEFORE_COMMIT_EVENT_PREFIX = 'ergnuor.domain_model.before_commit.';
    private const AFTER_TRANSACTION_EVENT_PREFIX = 'ergnuor.domain_model.after_transaction.';

    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatchBeforeTransaction(DomainEventInterface $event)
    {
        $this->doDispatch(self::BEFORE_TRANSACTION_EVENT_PREFIX, $event);
    }

    private function doDispatch(string $eventPrefix, DomainEventInterface $event)
    {
        $this->dispatcher->dispatch($event, $this->getEventNameWithPrefix($eventPrefix, get_class($event)));
    }

    public function getBeforeTransactionFullEventName(string $eventName): string
    {
        return $this->getEventNameWithPrefix(self::BEFORE_TRANSACTION_EVENT_PREFIX, $eventName);
    }

    public function getBeforeCommitFullEventName(string $eventName): string
    {
        return $this->getEventNameWithPrefix(self::BEFORE_COMMIT_EVENT_PREFIX, $eventName);
    }

    public function getAfterTransactionFullEventName(string $eventName): string
    {
        return $this->getEventNameWithPrefix(self::AFTER_TRANSACTION_EVENT_PREFIX, $eventName);
    }

    private function getEventNameWithPrefix(string $prefix, string $eventName): string
    {
        return $prefix . $eventName;
    }

    public function dispatchBeforeCommit(DomainEventInterface $event)
    {
        $this->doDispatch(self::BEFORE_COMMIT_EVENT_PREFIX, $event);
    }

    public function dispatchAfterTransaction(DomainEventInterface $event)
    {
        $this->doDispatch(self::AFTER_TRANSACTION_EVENT_PREFIX, $event);
    }

//    public function addBeforeTransactionListener(string $eventName, string $listenerServiceId, int $priority = 0)
//    {
//        $this->dispatcher->addListener(
//            $this->getEventNameWithPrefix(self::BEFORE_TRANSACTION_EVENT_PREFIX, $eventName),
//            $listenerServiceId,
//            $priority
//        );
//    }
//
//    public function addBeforeCommitListener(string $eventName, string $listenerServiceId, int $priority = 0)
//    {
//        $this->dispatcher->addListener(
//            $this->getEventNameWithPrefix(self::BEFORE_COMMIT_EVENT_PREFIX, $eventName),
//            $listenerServiceId,
//            $priority
//        );
//    }
//
//    public function addAfterTransactionListener(string $eventName, string $listenerServiceId, int $priority = 0)
//    {
//        $this->dispatcher->addListener(
//            $this->getEventNameWithPrefix(self::AFTER_TRANSACTION_EVENT_PREFIX, $eventName),
//            $listenerServiceId,
//            $priority
//        );
//    }
//
//    public function addBeforeTransactionSubscriber(string $subscriberClassName, ?string $subscriberServiceId = null)
//    {
//        $this->addSubscriber(
//            self::BEFORE_TRANSACTION_EVENT_PREFIX,
//            $subscriberClassName,
//            $subscriberServiceId
//        );
//    }
//
//    public function addBeforeCommitSubscriber(string $subscriberClassName, ?string $subscriberServiceId = null)
//    {
//        $this->addSubscriber(
//            self::BEFORE_COMMIT_EVENT_PREFIX,
//            $subscriberClassName,
//            $subscriberServiceId
//        );
//    }
//
//    public function addAfterTransactionSubscriber(string $subscriberClassName, ?string $subscriberServiceId = null)
//    {
//        $this->addSubscriber(
//            self::AFTER_TRANSACTION_EVENT_PREFIX,
//            $subscriberClassName,
//            $subscriberServiceId
//        );
//    }
//
//    private function addSubscriber(
//        string $eventPrefix,
//        string $subscriberClassName,
//        ?string $subscriberServiceId = null
//    ) {
//        if (!is_subclass_of($subscriberClassName, EntityManagerEventSubscriberInterface::class, true)) {
//            throw new \RuntimeException(sprintf("Instance of '%s' expected",
//                EntityManagerEventSubscriberInterface::class));
//        }
//
//        if ($subscriberServiceId === null) {
//            $subscriberServiceId = $subscriberClassName;
//        }
//
//        foreach ($subscriberClassName::getSubscribedEvents() as $eventName => $params) {
//            $eventNameWithPrefix = $this->getEventNameWithPrefix($eventPrefix, $eventName);
//
//            if (\is_string($params)) {
//                $this->dispatcher->addListener($eventNameWithPrefix, [$subscriberServiceId, $params]);
//            } elseif (\is_string($params[0])) {
//                $this->dispatcher->addListener(
//                    $eventNameWithPrefix,
//                    [$subscriberServiceId, $params[0]],
//                    $params[1] ?? 0
//                );
//            } else {
//                foreach ($params as $listener) {
//                    $this->dispatcher->addListener(
//                        $eventNameWithPrefix,
//                        [$subscriberServiceId, $listener[0]],
//                        $listener[1] ?? 0
//                    );
//                }
//            }
//        }
//    }
}