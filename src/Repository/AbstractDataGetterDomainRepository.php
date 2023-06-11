<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;


use Ergnuor\DataGetter\DataGetterInterface;

/**
 * @template TEntity
 * @template TExpression
 * @template TParameters
 * @template TOrder
 * @extends  AbstractMappedCriteriaDomainRepository<TEntity, TExpression, TParameters, TOrder>
 */
abstract class AbstractDataGetterDomainRepository extends AbstractMappedCriteriaDomainRepository
{
    /**
     * @inheritDoc
     */
    protected function doGetRawList(
        $mappedExpression,
        $mappedParameters,
        $mappedOrderBy,
        $limit = null,
        $offset = null
    ): array {
        $dataGetter = $this->createListDataGetter();

        $listResult = $dataGetter->getListResult(
            $mappedExpression,
            $mappedParameters,
            $mappedOrderBy,
            $limit,
            $offset,
        );

        return $this->transformListResultToArray($listResult);
    }

    abstract protected function createListDataGetter(): DataGetterInterface;

    /**
     * @param $listResult
     * @return array<array>
     */
    protected function transformListResultToArray($listResult): array
    {
        return $listResult;
    }

    /**
     * @inheritDoc
     */
    protected function doGetCount($mappedExpression, $mappedParameters): int
    {
        $dataGetter = $this->createCountDataGetter();

        return (int)$dataGetter->getScalarResult($mappedExpression, $mappedParameters);
    }

    abstract protected function createCountDataGetter(): DataGetterInterface;
}
