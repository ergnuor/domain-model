<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionBuilder as expr;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataAccess\ExpressionMapper\DoctrineExpressionMapper;
use Ergnuor\DomainModel\DataAccess\QueryExecutor\DoctrineQueryBuilderQueryExecutor;
use Doctrine\ORM\QueryBuilder;

trait DoctrineQueryBuilderTableDataGatewayTrait
{
    final protected function getRawList(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT
    ): array {
        $queryBuilder = $this->createListQueryBuilder();
        $list = $this->getQueryBuilderListResult($queryBuilder, $expression, $orderBy, $limit, $offset, $hydrationMode);

        if (empty($list)) {
            return [];
        }

        return $this->mapList($list);
    }

    abstract protected function createListQueryBuilder(): QueryBuilder;

    /**
     * @param QueryBuilder $queryBuilder
     * @param ExpressionInterface|null $expression
     * @param $limit
     * @param $offset
     * @return array
     */
    private function getQueryBuilderListResult(
        QueryBuilder $queryBuilder,
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT
    ): array {
        $queryExecutor = $this->createDoctrineQueryBuilderQueryExecutor(
            $queryBuilder,
            $this->getFieldMap(),
            $hydrationMode
        );

        $this->addConstantFilters($expression);

        return $queryExecutor->getListResult(
            $expression,
            $orderBy,
            $limit,
            $offset,
        );
    }

    private function createDoctrineQueryBuilderQueryExecutor(
        QueryBuilder $queryBuilder,
        ?array $fieldMap = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT,
    ): DoctrineQueryBuilderQueryExecutor {
        $mapper = new DoctrineExpressionMapper($fieldMap);

        $this->configureMapper($mapper);

        return new DoctrineQueryBuilderQueryExecutor(
            $queryBuilder,
            $mapper,
            $this->serializer,
            $hydrationMode
        );
    }

    protected function configureMapper(DoctrineExpressionMapper $mapper): void
    {
    }

    abstract protected function getFieldMap(): array;

    private function addConstantFilters(?ExpressionInterface $expression): ?ExpressionInterface
    {
        $constantFilters = $this->getConstantFilters();

        if ($constantFilters === null) {
            return $expression;
        }

        if ($expression === null) {
            return $constantFilters;
        }

        return expr::andX(
            $constantFilters,
            $expression
        );
    }

    protected function getConstantFilters(): ?ExpressionInterface
    {
        return null;
    }

    protected function mapList(array $list): array
    {
        return $list;
    }

    final protected function getRawListToFindOne(
        ?ExpressionInterface $expression = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT
    ): array {
        $queryBuilder = $this->createItemQueryBuilder();
        $list = $this->getQueryBuilderListResult($queryBuilder, $expression, null, null, null, $hydrationMode);

        foreach ($list as $key => $item) {
            $list[$key] = $this->mapItem($item);
        }

        return $list;
    }

    protected function createItemQueryBuilder(): QueryBuilder
    {
        return $this->createListQueryBuilder();
    }

    protected function mapItem(array $item): array
    {
        return $this->mapList([$item])[0];
    }

    protected function doCount(?ExpressionInterface $expression = null): int
    {
        $queryBuilder = $this->createCountQueryBuilder();
        $queryExecutor = $this->createDoctrineQueryBuilderQueryExecutor(
            $queryBuilder,
            $this->getFieldMap()
        );

        $this->addConstantFilters($expression);

        return (int)$queryExecutor->getScalarResult($expression);
    }

    abstract protected function createCountQueryBuilder(): QueryBuilder;
}