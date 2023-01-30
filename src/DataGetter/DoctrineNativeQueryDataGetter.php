<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NativeQuery;
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
use Symfony\Component\Serializer\Serializer;

class DoctrineNativeQueryDataGetter extends AbstractSQLDataGetter
{
    private int $hydrationMode;
    private NativeQuery $query;

    public function __construct(
        NativeQuery $query,
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
     * @return mixed
     */
    protected function doGetListResult(
        mixed $parameters,
        mixed $mappedExpression,
        mixed $mappedOrderBy,
        ?int $limit = null,
        ?int $offset = null,
    ): iterable {
        $query = $this->getQueryWithExpression($parameters, $mappedExpression);

        if ($mappedOrderBy !== null) {
            $orderBy = new OrderBy();
            foreach ($mappedOrderBy as $fieldName => $direction) {
                $orderBy->add($fieldName, $direction);
            }

            $query->setSQL(
                $this->injectOrderByIntoSql($query->getSQL(), (string)$orderBy)
            );
        }

        if (
            $limit !== null ||
            $offset !== null
        ) {
            $query->setSQL($this->modifyLimitQuery($query->getSQL(), $limit, $offset));
        }

        $query->setHydrationMode($this->hydrationMode);

        return $query->getResult();
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param Composite|Comparison|null $mappedExpression
     * @return NativeQuery
     */
    private function getQueryWithExpression(
        ArrayCollection $parameters,
        Composite|Comparison|null $mappedExpression,
    ): NativeQuery {
        $queryClone = $this->cloneQuery($this->query);

        if ($mappedExpression === null) {
            return $queryClone;
        }

        $queryClone->setSQL(
            $this->injectWhereIntoSql($queryClone->getSQL(), (string)$mappedExpression)
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

    private function cloneQuery(NativeQuery $query): NativeQuery
    {
        $cloneQuery = clone $query;

        $cloneQuery->setParameters(clone $query->getParameters());
        $cloneQuery->setCacheable(false);

        foreach ($query->getHints() as $name => $value) {
            $cloneQuery->setHint($name, $value);
        }

        return $cloneQuery;
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
        $query = $this->getQueryWithExpression($parameters, $mappedExpression);
        return $query->getSingleScalarResult();
    }
}