<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\QueryExecutor;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataAccess\ExpressionMapper\DoctrineExpressionMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDoctrineQueryExecutor implements QueryExecutorInterface
{
    private DoctrineExpressionMapper $expressionMapper;
    private ?Serializer $serializer;

    public function __construct(
        DoctrineExpressionMapper $expressionMapper,
        Serializer $serializer = null,
    ) {
        $this->expressionMapper = $expressionMapper;
        $this->serializer = $serializer;
    }

    final public function getListResult(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $mappedExpression = $this->mapExpression($expression);
        $result = $this->doGetListResult(
            $this->expressionMapper->getMappedParameters(),
            $mappedExpression,
            $limit,
            $offset
        );

        $resultAsArray = [];
        foreach ($result as $item) {

            if ($this->serializer !== null) {
                $resultAsArray[] = $this->serializer->normalize($item);
            } else {
                $resultAsArray[] = $item;
            }
        }

        return $resultAsArray;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param ExpressionInterface $expression
     * @return mixed|null
     */
    protected function mapExpression(?ExpressionInterface $expression): mixed
    {
        if ($expression !== null) {
            return $expression->visit($this->expressionMapper);
        }

        return null;
    }

    abstract protected function doGetListResult(
        ArrayCollection $parameters,
        mixed $mappedExpression = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array|\IteratorAggregate|\Countable;

    final public function getScalarResult(?ExpressionInterface $expression): mixed
    {
        $mappedExpression = $this->mapExpression($expression);
        return $this->doGetScalarResult(
            $this->expressionMapper->getMappedParameters(),
            $mappedExpression
        );
    }

    abstract protected function doGetScalarResult(ArrayCollection $parameters, mixed $mappedExpression = null): mixed;
}