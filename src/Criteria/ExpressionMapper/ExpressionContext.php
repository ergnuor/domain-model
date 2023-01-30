<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Ergnuor\DomainModel\Criteria\Type\TypeInferer;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

class ExpressionContext
{
    private string $fieldName;
    private mixed $value;
    private string $type;
    private string $operator;
    private ValueSourceInterface $valueSource;

    public function __construct(mixed $value, string $type, string $fieldName, string $operator, ValueSourceInterface $valueSource)
    {
        $this->value = $value;
        $this->type = $type;
        $this->fieldName = $fieldName;
        $this->operator = $operator;
        $this->valueSource = $valueSource;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValueSource(): ValueSourceInterface
    {
        return $this->valueSource;
    }

    public function withValue(mixed $value, string $type = null): ExpressionContext
    {
        $type = $type ?? TypeInferer::inferType($value);

        return new ExpressionContext(
            $value,
            $type,
            $this->getFieldName(),
            $this->getOperator(),
            $this->getValueSource(),
        );
    }
}