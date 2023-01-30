<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\OrderMapper;

use Stringable;

class ArrayOrderMapper extends AbstractOrderMapper
{
    protected function doMap(array $sort): array
    {
        $mappedSort = [];

        foreach ($sort as $fieldName => $direction) {
            if (!isset($this->fields[$fieldName])) {
                throw new \RuntimeException(
                    sprintf(
                        "Unknown order field '%s'",
                        $fieldName,
                    )
                );
            }

            $valueSource = $this->fields[$fieldName];

            if (!($valueSource instanceof Stringable)) {
                throw new \RuntimeException(
                    sprintf(
                        "'%s' value source expected",
                        Stringable::class,
                    )
                );
            }

            $mappedSort[(string)$valueSource] = $direction;
        }

        return $mappedSort;
    }
}