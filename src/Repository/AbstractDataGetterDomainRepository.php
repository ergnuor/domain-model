<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;


use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataGetter\DataGetterInterface;

abstract class AbstractDataGetterDomainRepository extends AbstractDomainRepository
{
    protected function getRawList(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        $limit = null,
        $offset = null
    ): array {
        $dataGetter = $this->createListDataGetter();

        return $dataGetter->getListResult(
            $expression,
            $orderBy,
            $limit,
            $offset,
        );
    }

    abstract protected function createListDataGetter(): DataGetterInterface;

    protected function doCount(?ExpressionInterface $expression = null): int
    {
        $dataGetter = $this->createCountDataGetter();

        return (int)$dataGetter->getScalarResult($expression);
    }

    abstract protected function createCountDataGetter(): DataGetterInterface;
}
