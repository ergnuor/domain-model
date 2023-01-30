<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Ergnuor\DomainModel\Criteria\Parameter;

class FieldMapResult
{
    private mixed $mappedExpression;

    /** @var ArrayCollection<Parameter> */
    private ArrayCollection $parameters;

    public function __construct(mixed $mappedExpression)
    {
        $this->mappedExpression = $mappedExpression;
        $this->parameters = new ArrayCollection();
    }

    public function addParameter(Parameter $parameter): void
    {
        $this->parameters->add($parameter);
    }

    public function getMappedExpression(): mixed
    {
        return $this->mappedExpression;
    }

    public function getParameters(): ArrayCollection
    {
        return $this->parameters;
    }
}