<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;

class Registry implements RegistryInterface
{
    private EntityManagerInterface $domainEntityManager;
    private JsonSerializerInterface $repositorySerializer;
    private JsonSerializerInterface $tableDataGatewaySerializer;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        JsonSerializerInterface $repositorySerializer,
        JsonSerializerInterface $tableDataGatewaySerializer
    ) {
        $this->domainEntityManager = $domainEntityManager;
        $this->repositorySerializer = $repositorySerializer;
        $this->tableDataGatewaySerializer = $tableDataGatewaySerializer;
    }

    public function getDomainEntityManager(): EntityManagerInterface
    {
        return $this->domainEntityManager;
    }

    public function getDomainEntitySerializer(): JsonSerializerInterface
    {
        return $this->repositorySerializer;
    }

    public function getTableDataGatewayDTOSerializer(): JsonSerializerInterface
    {
        return $this->tableDataGatewaySerializer;
    }
}