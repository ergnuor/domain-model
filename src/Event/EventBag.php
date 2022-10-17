<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Event;

class EventBag
{
    private array $events = [];

    public function add(DomainEventInterface $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return DomainEventInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }
}