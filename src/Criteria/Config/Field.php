<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Config;

use Ergnuor\DomainModel\Criteria\Config\Field\Filter;
use Ergnuor\DomainModel\Criteria\Config\Field\Sorting;

class Field
{
    private ?Filter $filter;
    private ?Sorting $sorting;
    private string $fieldName;

    public function __construct(string $fieldName, ?Filter $filter, ?Sorting $sorting)
    {
        $this->fieldName = $fieldName;
        $this->filter = $filter;
        $this->sorting = $sorting;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function getSorting(): ?Sorting
    {
        return $this->sorting;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}