<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Entity;

use Ergnuor\DomainModel\Event\EventBag;

interface DomainAggregateInterface extends DomainEntityInterface
{
    public function getEventBag(): EventBag;
}