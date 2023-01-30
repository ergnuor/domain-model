<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\Criteria\ConfigBuilder\ConfigBuilder;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

interface RegistryInterface
{
    public function getDomainEntityManager(): EntityManagerInterface;

    public function getDomainEntitySerializer(): Serializer;

    public function getTableDataGatewayDTOSerializer(): Serializer;

    public function getConfigBuilder(): ConfigBuilder;

    public function getExpressionMapperContainer(): ContainerInterface;
}