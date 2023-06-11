<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel;

use Ergnuor\Criteria\ConfigBuilder\ConfigBuilder;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

class Registry implements RegistryInterface
{
    private EntityManagerInterface $domainEntityManager;
    private Serializer $repositorySerializer;
    private ConfigBuilder $configBuilder;
    private ContainerInterface $expressionMapperContainer;

    public function __construct(
        EntityManagerInterface $domainEntityManager,
        Serializer $repositorySerializer,
        ConfigBuilder $configBuilder,
        ContainerInterface $expressionMapperContainer
    ) {
        $this->domainEntityManager = $domainEntityManager;
        $this->repositorySerializer = $repositorySerializer;
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

    public function getConfigBuilder(): ConfigBuilder
    {
        return $this->configBuilder;
    }

    public function getExpressionMapperContainer(): ContainerInterface
    {
        return $this->expressionMapperContainer;
    }
}