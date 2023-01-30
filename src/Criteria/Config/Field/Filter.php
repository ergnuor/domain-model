<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Config\Field;

use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

class Filter
{
    private ?ValueSourceInterface $valueSource;
    private null|string|FieldExpressionMapperInterface $fieldExpressionMapper;

    public function __construct(
        ?ValueSourceInterface $valueSource,
        null|string|FieldExpressionMapperInterface $fieldExpressionMapper
    ) {
        $this->valueSource = $valueSource;
        $this->fieldExpressionMapper = $fieldExpressionMapper;
    }

    public function getValueSource(): ?ValueSourceInterface
    {
        return $this->valueSource;
    }

    public function getFieldExpressionMapper(): null|string|FieldExpressionMapperInterface
    {
        return $this->fieldExpressionMapper;
    }
}