<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\OrderMapper;

use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

abstract class AbstractOrderMapper implements OrderMapperInterface
{
    /** @var array<string, ValueSourceInterface> */
    protected array $fields = [];

    public function addField(
        string $fieldName,
        ?ValueSourceInterface $valueSource,
    ): void {
        $this->fields[$fieldName] = $valueSource;
    }

    public function map(array $sort): array
    {
        $sort = $this->normalizeSort($sort);

        return $this->doMap($sort);
    }

    private function normalizeSort(array $sort): array
    {
        $normalizedSort = [];

        foreach ($sort as $fieldName => $direction) {
            $direction = strtolower($direction) === OrderMapperInterface::ASC ?
                OrderMapperInterface::ASC : OrderMapperInterface::DESC;

            $normalizedSort[(string)$fieldName] = $direction;
        }

        return $normalizedSort;
    }

    abstract protected function doMap(array $sort): mixed;
}