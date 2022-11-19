<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;

interface RegistryInterface
{
    public function getDomainEntityManager(): EntityManagerInterface;

    public function getDomainEntitySerializer(): Serializer;

    public function getTableDataGatewayDTOSerializer(): Serializer;
}