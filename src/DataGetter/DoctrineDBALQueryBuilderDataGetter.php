<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\Parameter;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineDBALExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Serializer;

class DoctrineDBALQueryBuilderDataGetter extends AbstractSQLDataGetter
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        QueryBuilder $queryBuilder,
        DoctrineDBALExpressionMapper $expressionMapper,
        OrderMapperInterface $orderMapper,
        Connection $connection,
        ?Serializer $serializer = null,
    ) {
        parent::__construct(
            $connection,
            $expressionMapper,
            $orderMapper,
            $serializer
        );
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param ArrayCollection $parameters
     * @param CompositeExpression|string|null $mappedExpression
     * @param array|null $mappedOrderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws Exception
     */
    protected function doGetListResult(
        mixed $parameters,
        mixed $mappedExpression,
        mixed $mappedOrderBy,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $queryBuilder = $this->getQueryBuilderWithExpression($parameters, $mappedExpression);

        if ($mappedOrderBy !== null) {
            foreach ($mappedOrderBy as $fieldName => $direction) {
                $queryBuilder->addOrderBy($fieldName, $direction);
            }
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param CompositeExpression|string|null $mappedExpression
     * @return QueryBuilder
     */
    private function getQueryBuilderWithExpression(
        ArrayCollection $parameters,
        CompositeExpression|string|null $mappedExpression
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
                $parameter->getType()
            );
        }

        return $queryBuilderClone;
    }

    /**
     * @param ArrayCollection<Parameter> $parameters
     * @param CompositeExpression|string|null $mappedExpression
     * @return mixed
     * @throws Exception
     */
    protected function doGetScalarResult(
        mixed $parameters,
        mixed $mappedExpression
    ): mixed {
        $queryBuilder = $this->getQueryBuilderWithExpression($parameters, $mappedExpression);
        return $queryBuilder->executeQuery()->fetchOne();
    }
}