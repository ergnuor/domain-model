<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;

/**
 * @template T
 */
interface TableDataGatewayInterface
{
    /**
     * @param mixed $id The identifier.
     *
     * @return T|null The object.
     *
     * @psalm-return T|null
     */
    public function findById(mixed $id): ?object;

    /**
     * @param ExpressionInterface|array|null $expression
     * @param string[]|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return T<object>
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
     * @param ExpressionInterface|array $expression
     *
     * @return T|null The object.
     *
     * @psalm-return T|null
     */
    public function findOneBy(array|ExpressionInterface $expression): ?object;

    public function count(array|ExpressionInterface|null $expression = null): int;
}