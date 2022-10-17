<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\DataAccess\ExpressionBuilder\ExpressionTrait;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;

/**
 * @template T
 */
trait RepositoryTrait
{
    use ExpressionTrait;

    /**
     * {@inheritDoc}
     */
    public function findById(mixed $id): ?object
    {
        return $this->findOneBy($this->getIdCriteria($id));
    }

    protected function getIdCriteria($id): array
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
        return $this->doFindBy(
            $this->normalizeExpression($expression),
            $orderBy,
            $limit,
            $offset
        );

    }

    /**
     * @param ExpressionInterface|null $expression
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<T>
     */
    abstract protected function doFindBy(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    final public function findOneBy(array|ExpressionInterface $expression): ?object
    {
        $result = $this->findBy($this->normalizeExpression($expression));

        if (count($result) > 1) {
            throw new \RuntimeException('More than one item returned. Expecting one or zero items');
        }

        if (count($result) == 0) {
            return null;
        }

        return array_shift($result);
    }

    public function count(array|ExpressionInterface|null $expression = null): int
    {
        return $this->doCount(
            $this->normalizeExpression($expression)
        );
    }

    abstract protected function doCount(?ExpressionInterface $expression = null): int;
}
