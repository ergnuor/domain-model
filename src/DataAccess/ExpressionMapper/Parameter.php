<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

class Parameter
{
    private string $name;

    /**
     * @var mixed
     */
    private $value;

    private string $type;

    public function __construct($name, $value, $type = null)
    {
        $this->name = $name;

        $this->setValue($value, $type);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setValue($value, $type = null)
    {
        $this->value = $value;
        $this->type = $type ?: ParameterTypeInferer::inferType($value);
    }
}
