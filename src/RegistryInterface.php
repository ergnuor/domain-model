<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;

interface RegistryInterface
{
    public function getDomainEntityManager(): EntityManagerInterface;

    public function getDomainEntitySerializer(): JsonSerializerInterface;

    public function getTableDataGatewayDTOSerializer(): JsonSerializerInterface;
}