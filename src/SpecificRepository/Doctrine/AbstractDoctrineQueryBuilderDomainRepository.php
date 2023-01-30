<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\SpecificRepository\Doctrine;


use Doctrine\ORM\QueryBuilder;
use Ergnuor\DomainModel\Criteria\Config\Config;
use Ergnuor\DomainModel\Criteria\ConfigBuilder\ConfigBuilder;
use Ergnuor\DomainModel\Criteria\OrderMapper\ArrayOrderMapper;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper;
use Ergnuor\DomainModel\DataGetter\DoctrineQueryLanguageQueryBuilderDataGetter;
use Ergnuor\DomainModel\DataGetter\DataGetterInterface;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Ergnuor\DomainModel\Repository\AbstractDataGetterDomainRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDoctrineQueryBuilderDomainRepository extends AbstractDataGetterDomainRepository
{
    protected Config $fieldsConfig;
    protected ?ContainerInterface $expressionMapperContainer;

    public function __construct(
        string $className,
        EntityManagerInterface $domainEntityManager,
        Serializer $serializer,
        ConfigBuilder $configBuilder,
        ?ContainerInterface $expressionMapperContainer = null
    ) {
        parent::__construct($className, $domainEntityManager, $serializer);

        $this->buildFieldsConfig($configBuilder);
        $this->expressionMapperContainer = $expressionMapperContainer;
    }

    private function buildFieldsConfig(ConfigBuilder $configBuilder): void
    {
        $this->fieldsConfig = $configBuilder->build(
            $this->getFieldsConfig()
        );

    }

    abstract protected function getFieldsConfig(): array;

    protected function createListDataGetter(): DataGetterInterface
    {
        $queryBuilder = $this->createListQueryBuilder();

        return $this->createDoctrineQueryBuilderDataGetter($queryBuilder);
    }

    abstract protected function createListQueryBuilder(): QueryBuilder;

    private function createDoctrineQueryBuilderDataGetter(
        QueryBuilder $queryBuilder
    ): DoctrineQueryLanguageQueryBuilderDataGetter {
        $expressionMapper = new DoctrineORMExpressionMapper(
            $this->expressionMapperContainer
        );
        $this->configureExpressionMapper($expressionMapper);

        $orderMapper = new ArrayOrderMapper();
        $this->configureOrderMapper($orderMapper);

        return new DoctrineQueryLanguageQueryBuilderDataGetter(
            $queryBuilder,
            $expressionMapper,
            $orderMapper,
            $this->getDoctrineQueryBuilderSerializer(),
        );
    }

    protected function configureExpressionMapper(DoctrineORMExpressionMapper $mapper): void
    {
        $this->fieldsConfig->configureExpressionMapper($mapper);
    }

    protected function configureOrderMapper(ArrayOrderMapper $orderMapper): void
    {
        $this->fieldsConfig->configureOrderMapper($orderMapper);
    }

    protected function getDoctrineQueryBuilderSerializer(): Serializer
    {
        return $this->serializer;
    }

    protected function createCountDataGetter(): DataGetterInterface
    {
        $queryBuilder = $this->createCountQueryBuilder();

        return $this->createDoctrineQueryBuilderDataGetter($queryBuilder);
    }

    abstract protected function createCountQueryBuilder(): QueryBuilder;
}
