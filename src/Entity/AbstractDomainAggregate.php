<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Entity;

use Ergnuor\DomainModel\Event\EventBag;
use Ergnuor\DomainModel\Mapping\Annotation;

abstract class AbstractDomainAggregate implements DomainAggregateInterface
{
    #[Annotation\Internal]
    private ?EventBag $eventBag = null;

    final public function getEventBag(): EventBag
    {
        if ($this->eventBag === null) {
            $this->eventBag = new EventBag();
        }

        return $this->eventBag;
    }
}