<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;

interface DataGetterInterface
{
    public function getListResult(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array;

    public function getScalarResult(?ExpressionInterface $expression): mixed;
}