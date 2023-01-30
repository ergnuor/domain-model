<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\Parameter;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Serializer;

class DoctrineQueryLanguageQueryDataGetter extends AbstractSQLDataGetter
{
    private int $hydrationMode;
    private Query|QueryBuilder $query;

    public function __construct(
        Query $query,
        DoctrineORMExpressionMapper $expressionMapper,
        OrderMapperInterface $orderMapper,
        ?Serializer $serializer = null,
        int $hydrationMode = AbstractQuery::HYDRATE_OBJECT,
    ) {
        parent::__construct(
            $query->getEntityManager()->getConnection(),
            $expressionMapper,
            $orderMapper,
            $serializer
        );
        $this->query = $query;
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
        $query = $this->getQueryWithExpression($parameters, $mappedExpression);
        if ($mappedOrderBy !== null) {
            $orderBy = new OrderBy();
            foreach ($mappedOrderBy as $fieldName => $direction) {
                $orderBy->add($fieldName, $direction);
            }

            $query->setDQL(
                $this->injectOrderByIntoSql($query->getDQL(), (string)$orderBy)
            );
        }

        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        $query->setHydrationMode($this->hydrationMode);
        return new Paginator($query);
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param Composite|Comparison|null $mappedExpression
     * @return Query
     */
    private function getQueryWithExpression(
        ArrayCollection $parameters,
        Composite|Comparison|null $mappedExpression,
    ): Query {
        $queryClone = $this->cloneQuery($this->query);

        if ($mappedExpression === null) {
            return $queryClone;
        }

        $queryClone->setDQL(
            $this->injectWhereIntoSql($queryClone->getDQL(), (string)$mappedExpression)
        );

        foreach ($parameters as $parameter) {
            $queryClone->setParameter(
                $parameter->getName(),
                $parameter->getValue(),
                $parameter->getType(),
            );
        }

        return $queryClone;
    }

    private function cloneQuery(Query $query): Query
    {
        $queryClone = clone $query;

        $queryClone->setParameters(clone $query->getParameters());
        $queryClone->setCacheable(false);

        foreach ($query->getHints() as $name => $value) {
            $queryClone->setHint($name, $value);
        }

        return $queryClone;
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
        return $this->getQueryWithExpression($parameters, $mappedExpression)->getSingleScalarResult();
    }
}