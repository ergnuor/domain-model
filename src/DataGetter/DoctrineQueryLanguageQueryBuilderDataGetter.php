<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Parameter;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Serializer;

class DoctrineQueryLanguageQueryBuilderDataGetter extends AbstractSQLDataGetter
{
    private int $hydrationMode;
    private QueryBuilder $queryBuilder;

    public function __construct(
        QueryBuilder $queryBuilder,
        DoctrineORMExpressionMapper $expressionMapper,
        OrderMapperInterface $orderMapper,
        ?Serializer $serializer = null,
        int $hydrationMode = AbstractQuery::HYDRATE_OBJECT,
    ) {
        parent::__construct(
            $queryBuilder->getEntityManager()->getConnection(),
            $expressionMapper,
            $orderMapper,
            $serializer
        );
        $this->queryBuilder = $queryBuilder;
        $this->hydrationMode = $hydrationMode;
    }

    /**
     * @param ArrayCollection $parameters
     * @param Composite|Comparison|null $mappedExpression
     * @param array|null $mappedOrderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Paginator
     */
    protected function doGetListResult(
        mixed $parameters,
        mixed $mappedExpression,
        mixed $mappedOrderBy,
        ?int $limit = null,
        ?int $offset = null,
    ): Paginator {
        $queryBuilder = $this->getQueryBuilderWithExpression($parameters, $mappedExpression);

        if ($mappedOrderBy !== null) {
            foreach($mappedOrderBy as $fieldName => $direction) {
                $queryBuilder->addOrderBy($fieldName, $direction);
            }
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();
        $query->setHydrationMode($this->hydrationMode);

        return new Paginator($query);
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param Composite|Comparison|null $mappedExpression
     * @return QueryBuilder
     */
    private function getQueryBuilderWithExpression(
        ArrayCollection $parameters,
        Composite|Comparison|null $mappedExpression,
    ): QueryBuilder {
        $queryBuilderClone = clone $this->queryBuilder;

        if ($mappedExpression === null) {
            return $queryBuilderClone;
        }

        $queryBuilderClone->where($mappedExpression);

        foreach ($parameters as $parameter) {
            $queryBuilderClone->setParameter(
                $parameter->getName(),
                $parameter->getValue(),
                $parameter->getType(),
            );
        }

        return $queryBuilderClone;
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param Composite|Comparison|null $mappedExpression
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function doGetScalarResult(
        mixed $parameters,
        mixed $mappedExpression
    ): mixed {
        $queryBuilder = $this->getQueryBuilderWithExpression($parameters, $mappedExpression);
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}