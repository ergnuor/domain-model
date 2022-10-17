<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionBuilder as expr;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataAccess\ExpressionMapper\DoctrineExpressionMapper;
use Ergnuor\DomainModel\DataAccess\QueryExecutor\DoctrineQueryBuilderQueryExecutor;
use Doctrine\ORM\QueryBuilder;

trait DoctrineQueryBuilderRepositoryTrait
{
    protected function getRawList(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT
    ): array {
        $queryBuilder = $this->createListQueryBuilder();

        $queryExecutor = $this->createDoctrineQueryBuilderQueryExecutor(
            $queryBuilder,
            $this->getFieldMap(),
            $hydrationMode
        );

        $this->addConstantFilters($expression);

        $list = $queryExecutor->getListResult(
            $expression,
            $orderBy,
            $limit,
            $offset,
        );

        if (empty($list)) {
            return [];
        }

        return $this->mapList($list);
    }

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

    abstract protected function createListQueryBuilder(): QueryBuilder;

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

    abstract protected function getFieldMap(): array;

    protected function configureMapper(DoctrineExpressionMapper $mapper): void
    {
    }

    protected function getConstantFilters(): ?ExpressionInterface
    {
        return null;
    }

    protected function mapList(array $list): array
    {
        return $list;
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