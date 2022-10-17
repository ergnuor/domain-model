<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;

abstract class AbstractDomainRepository implements DomainRepositoryInterface
{
    use DomainRepositoryTrait;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        JsonSerializerInterface $serializer
    )
    {
        $this->domainEntityManager = $domainEntityManager;
        $this->serializer = $serializer;
    }
}
