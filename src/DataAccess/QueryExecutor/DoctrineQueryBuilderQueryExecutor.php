<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\QueryExecutor;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataAccess\ExpressionMapper\DoctrineExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Serializer;

class DoctrineQueryBuilderQueryExecutor extends AbstractDoctrineQueryExecutor
{
    private int $hydrationMode;
    private QueryBuilder $queryBuilder;

    public function __construct(
        QueryBuilder $queryBuilder,
        DoctrineExpressionMapper $expressionMapper,
        Serializer $serializer = null,
        int $hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT,
    ) {
        parent::__construct($expressionMapper, $serializer);
        $this->queryBuilder = $queryBuilder;
        $this->hydrationMode = $hydrationMode;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ExpressionInterface $expression
     * @return void
     */
    protected function addQueryBuilderFilters(
        QueryBuilder $queryBuilder,
        ArrayCollection $parameters,
        mixed $mappedExpression = null,
    ): void {
        if ($mappedExpression !== null) {
            if (count($parameters) > 0) {
                $queryBuilder->setParameters($parameters);
            }

            $queryBuilder->where($mappedExpression);
        }
    }

    protected function doGetListResult(
        ArrayCollection $parameters,
        mixed $mappedExpression = null,
        ?int $limit = null,
        ?int $offset = null,
    ): Paginator {
        $this->addQueryBuilderFilters($this->queryBuilder, $parameters, $mappedExpression);

        if ($offset !== null) {
            $this->queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $this->queryBuilder->setMaxResults($limit);
        }

        $query = $this->queryBuilder->getQuery();

        $query->setHydrationMode($this->hydrationMode);

        return new Paginator($query, true);
    }

    protected function doGetScalarResult(ArrayCollection $parameters, mixed $mappedExpression = null): mixed
    {
        $this->addQueryBuilderFilters($this->queryBuilder, $parameters, $mappedExpression);
        return $this->queryBuilder->getQuery()->getSingleScalarResult();
    }
}