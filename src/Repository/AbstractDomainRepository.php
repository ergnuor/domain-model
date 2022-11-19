<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDomainRepository implements DomainRepositoryInterface
{
    use DomainRepositoryTrait;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        Serializer $serializer
    )
    {
        $this->domainEntityManager = $domainEntityManager;
        $this->serializer = $serializer;
    }
}
