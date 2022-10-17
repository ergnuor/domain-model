<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\QueryExecutor;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;

interface QueryExecutorInterface
{
    public function getListResult(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array;

    public function getScalarResult(?ExpressionInterface $expression): mixed;
}