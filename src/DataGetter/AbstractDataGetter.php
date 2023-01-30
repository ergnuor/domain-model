<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDataGetter implements DataGetterInterface
{
    private ExpressionMapperInterface $expressionMapper;
    private ?Serializer $serializer;
    private OrderMapperInterface $orderMapper;

    public function __construct(
        ExpressionMapperInterface $expressionMapper,
        OrderMapperInterface $orderMapper,
        ?Serializer $serializer = null,
    ) {
        $this->expressionMapper = $expressionMapper;
        $this->orderMapper = $orderMapper;
        $this->serializer = $serializer;
    }

    final public function getListResult(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $mappedExpression = $this->mapExpression($expression);
        $mappedOrderBy = $this->mapOrder($orderBy);

        $result = $this->doGetListResult(
            $this->expressionMapper->getMappedParameters(),
            $mappedExpression,
            $mappedOrderBy,
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

    private function mapOrder(?array $orderBy): mixed
    {
        if ($orderBy === null) {
            return null;
        }

        return $this->orderMapper->map($orderBy);
    }

    /**
     * @param ExpressionInterface|null $expression
     * @return mixed|null
     */
    protected function mapExpression(?ExpressionInterface $expression): mixed
    {
        return $expression?->visit($this->expressionMapper);

    }

    /**
     * @param mixed $parameters
     * @param mixed $mappedExpression
     * @param array|null $mappedOrderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return iterable
     */
    abstract protected function doGetListResult(
        mixed $parameters,
        mixed $mappedExpression,
        mixed $mappedOrderBy,
        ?int $limit = null,
        ?int $offset = null,
    ): iterable;

    final public function getScalarResult(?ExpressionInterface $expression): mixed
    {
        $mappedExpression = $this->mapExpression($expression);

        return $this->doGetScalarResult(
            $this->expressionMapper->getMappedParameters(),
            $mappedExpression
        );
    }

    abstract protected function doGetScalarResult(mixed $parameters, mixed $mappedExpression): mixed;
}