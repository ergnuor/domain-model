<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ValueSource;

use Stringable;

class FieldValueSource implements ValueSourceInterface, Stringable
{
    private string $fieldName;

    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function __toString()
    {
        return $this->getFieldName();
    }
}