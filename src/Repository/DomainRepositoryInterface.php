<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;

/**
 * @template T
 */
interface DomainRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return T|null The object.
     *
     * @psalm-return T|null
     */
    public function findById(mixed $id): ?object;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array|ExpressionInterface|null $expression
     * @param string[]|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<T> The objects.
     *
     * @psalm-return T[]
     */
    public function findBy(
        array|ExpressionInterface|null $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array|ExpressionInterface $expression The criteria.
     *
     * @return T|null The object.
     *
     * @psalm-return T|null
     */
    public function findOneBy(array|ExpressionInterface $expression): ?object;

    public function count(array|ExpressionInterface|null $expression = null): int;

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName(): string;
}
