<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

use Ergnuor\DomainModel\EntityManager\UnitOfWork;

class Event
{
    private UnitOfWork $unitOfWork;

    public function __construct(UnitOfWork $unitOfWork)
    {
        $this->unitOfWork = $unitOfWork;
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }
}