<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\DomainModel\Criteria\ConfigBuilder\ConfigBuilder;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

class Registry implements RegistryInterface
{
    private EntityManagerInterface $domainEntityManager;
    private Serializer $repositorySerializer;
    private Serializer $tableDataGatewaySerializer;
    private ConfigBuilder $configBuilder;
    private ContainerInterface $expressionMapperContainer;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        Serializer $repositorySerializer,
        Serializer $tableDataGatewaySerializer,
        ConfigBuilder $configBuilder,
        ContainerInterface $expressionMapperContainer
    ) {
        $this->domainEntityManager = $domainEntityManager;
        $this->repositorySerializer = $repositorySerializer;
        $this->tableDataGatewaySerializer = $tableDataGatewaySerializer;
        $this->configBuilder = $configBuilder;
        $this->expressionMapperContainer = $expressionMapperContainer;
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

    public function getConfigBuilder(): ConfigBuilder
    {
        return $this->configBuilder;
    }

    public function getExpressionMapperContainer(): ContainerInterface
    {
        return $this->expressionMapperContainer;
    }
}