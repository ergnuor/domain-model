<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\Parameter;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineDBALExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Serializer;

class DoctrineDBALDataGetter extends AbstractSQLDataGetter
{
    private string $sql;

    public function __construct(
        string $sql,
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
        $this->sql = $sql;
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
        $sql = $this->getSqlWithExpression($mappedExpression);

        if ($mappedOrderBy !== null) {
            $orderBy = new OrderBy();
            foreach ($mappedOrderBy as $fieldName => $direction) {
                $orderBy->add($fieldName, $direction);
            }

            $sql = $this->injectOrderByIntoSql($sql, (string)$orderBy);
        }

        if (
            $limit !== null ||
            $offset !== null
        ) {
            $sql = $this->modifyLimitQuery($sql, $limit, $offset);
        }

        return $this->executeWithParameters($sql, $parameters)->fetchAllAssociative();
    }

    /**
     * @param string $query
     * @param ArrayCollection<Parameter> $parameters
     * @return Result
     * @throws Exception
     */
    private function executeWithParameters(string $query, ArrayCollection $parameters): Result
    {
        $params = [];
        $paramTypes = [];

        foreach ($parameters as $parameter) {
            $params[$parameter->getName()] = $parameter->getValue();
            $paramTypes[$parameter->getName()] = $parameter->getType();
        }

        return $this->connection->executeQuery(
            $query,
            $params,
            $paramTypes,
        );
    }

    private function getSqlWithExpression(CompositeExpression|string|null $mappedExpression): string
    {
        if ($mappedExpression === null) {
            return $this->sql;
        }

        return $this->injectWhereIntoSql($this->sql, (string)$mappedExpression);
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
        $sql = $this->getSqlWithExpression($mappedExpression);
        return $this->executeWithParameters($sql, $parameters)->fetchOne();
    }
}