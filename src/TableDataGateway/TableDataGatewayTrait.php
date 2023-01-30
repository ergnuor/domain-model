<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\ExpressionHelper\ExpressionNormalizer;
use Symfony\Component\Serializer\Serializer;

trait TableDataGatewayTrait
{
    protected Serializer $serializer;

    public function findById(mixed $id): ?object
    {
        return $this->findOneBy($this->getIdExpression($id));
    }

    /**
     * {@inheritDoc}
     */
    final public function findOneBy(array|ExpressionInterface $expression = null): ?object
    {
        $list = $this->getRawListToFindOne(ExpressionNormalizer::normalize($expression));

        if (count($list) > 1) {
            throw new \RuntimeException('More than one item returned. Expecting one or zero items');
        }

        if (count($list) == 0) {
            return null;
        }

        return $this->denormalizeItem(array_shift($list));
    }

    /**
     * @param ExpressionInterface|null $expression
     * @return array|null
     */
    protected function getRawListToFindOne(?ExpressionInterface $expression = null): array
    {
        return $this->getRawList(ExpressionNormalizer::normalize($expression));
    }

    /**
     * @param ExpressionInterface|null $expression
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<object>
     */
    abstract protected function getRawList(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    protected function denormalizeItem(array $item)
    {
        return $this->serializer->denormalize($item, $this->getItemDTOClassName());
    }

    protected function getItemDTOClassName(): string
    {
        return $this->getDTOClassName();
    }

    abstract protected function getDTOClassName(): string;

    protected function getIdExpression($id): array
    {
        return [
            $this->getIdFieldName() => $id
        ];
    }

    abstract protected function getIdFieldName(): string;

    /**
     * {@inheritDoc}
     */
    final public function findBy(
        array|ExpressionInterface|null $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $rawList = $this->getRawList(
            ExpressionNormalizer::normalize($expression),
            $orderBy,
            $limit,
            $offset
        );

        return $this->denormalizeList($rawList);
    }

    protected function denormalizeList(array $rawList)
    {
        return $this->serializer->denormalize($rawList, $this->getDTOClassName() . '[]');
    }

    public function count(array|ExpressionInterface|null $expression = null): int
    {
        return $this->doCount(
            ExpressionNormalizer::normalize($expression)
        );
    }

    abstract protected function doCount(?ExpressionInterface $expression = null): int;
}
