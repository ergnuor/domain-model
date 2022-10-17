<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use Ergnuor\DomainModel\DataAccess\ExpressionMapper\ExpressionMapperInterface;

class Value implements VisitableExpressionInterface
{
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function visit(ExpressionMapperInterface $visitor): mixed
    {
        return $visitor->walkValue($this);
    }
}
