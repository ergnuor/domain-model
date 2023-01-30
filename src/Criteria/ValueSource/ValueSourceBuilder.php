<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ValueSource;

class ValueSourceBuilder
{
    public function field(string $fieldName): FieldValueSource
    {
        return new FieldValueSource($fieldName);
    }

    public function expression(string $expression): ExpressionValueSource
    {
        return new ExpressionValueSource($expression);
    }
}