<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\OrderMapper;

use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

interface OrderMapperInterface
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * @param array<string, string> $sort
     * @return mixed
     */
    public function map(array $sort): mixed;

    public function addField(string $fieldName, ?ValueSourceInterface $valueSource): void;
}