<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria;

use Ergnuor\DomainModel\Criteria\Type\TypeInferer;

class Parameter
{
    private string $name;

    private mixed $value;

    private string $type;

    public function __construct(string $name, mixed $value, $type = null)
    {
        $this->name = $name;

        $this->setValue($value, $type);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setValue($value, $type = null): void
    {
        $this->value = $value;
        $this->type = $type ?: TypeInferer::inferType($value);
    }
}
