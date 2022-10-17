<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork;

class LifecycleEvent extends Event
{
    private DomainAggregateInterface $aggregate;

    public function __construct(UnitOfWork $unitOfWork, DomainAggregateInterface $aggregate)
    {
        parent::__construct($unitOfWork);
        $this->aggregate = $aggregate;
    }

    public function getAggregate(): DomainAggregateInterface
    {
        return $this->aggregate;
    }
}