<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\Criteria\Expression\ExpressionInterface;

/**
 * @template TEntity
 */
interface DomainRepositoryInterface
{
    /**
     * @param mixed $id
     * @return TEntity|null
     */
    public function findById(mixed $id): ?object;

    /**
     * @param array|ExpressionInterface|null $expression
     * @param string[]|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<TEntity>
     */
    public function findBy(
        array|ExpressionInterface|null $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * @param array|ExpressionInterface $expression
     * @return TEntity|null
     */
    public function findOneBy(array|ExpressionInterface $expression): ?object;

    public function count(array|ExpressionInterface|null $expression = null): int;

    public function getClassName(): string;
}
