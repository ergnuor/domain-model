<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

class Field
{
    private ValueSourceInterface $valueSource;
    private string|FieldExpressionMapperInterface $fieldExpressionMapper;

    public function __construct(ValueSourceInterface $valueSource, string|FieldExpressionMapperInterface $fieldExpressionMapper)
    {
        $this->valueSource = $valueSource;
        $this->fieldExpressionMapper = $fieldExpressionMapper;
    }

    public function getValueSource(): ValueSourceInterface
    {
        return $this->valueSource;
    }

    public function getFieldExpressionMapper(): string|FieldExpressionMapperInterface
    {
        return $this->fieldExpressionMapper;
    }
}