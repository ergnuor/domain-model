<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;

class Registry implements RegistryInterface
{
    private EntityManagerInterface $domainEntityManager;
    private Serializer $repositorySerializer;
    private Serializer $tableDataGatewaySerializer;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        Serializer $repositorySerializer,
        Serializer $tableDataGatewaySerializer
    ) {
        $this->domainEntityManager = $domainEntityManager;
        $this->repositorySerializer = $repositorySerializer;
        $this->tableDataGatewaySerializer = $tableDataGatewaySerializer;
    }

    public function getDomainEntityManager(): EntityManagerInterface
    {
        return $this->domainEntityManager;
    }

    public function getDomainEntitySerializer(): Serializer
    {
        return $this->repositorySerializer;
    }

    public function getTableDataGatewayDTOSerializer(): Serializer
    {
        return $this->tableDataGatewaySerializer;
    }
}