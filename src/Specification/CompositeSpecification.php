<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Specification;

use Ergnuor\Criteria\Expression\CompositeExpression;
use Ergnuor\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Entity\DomainAggregateInterface;

class CompositeSpecification extends AbstractSpecification
{
    public const TYPE_AND = 'AND';
    public const TYPE_OR = 'OR';

    private string $type;
    /** @var array<SpecificationInterface> */
    private array $specifications;

    /**
     * @param string $type
     * @param array<SpecificationInterface> $specifications
     */
    public function __construct(string $type, array $specifications)
    {
        $this->setType($type);

        foreach ($specifications as $specification) {
            if (!($specification instanceof SpecificationInterface)) {
                throw new \RuntimeException(
                    sprintf(
                        'The class "%s" must implement "%s" interface.',
                        get_debug_type($specification),
                        SpecificationInterface::class
                    )
                );
            }

            $this->specifications[] = $specification;
        }
    }

    private function setType(string $type)
    {
        if (!in_array($type, [self::TYPE_AND, self::TYPE_OR])) {
            throw new \RuntimeException("Unsupported composite specification type '{$type}'");
        }

        $this->type = $type;
    }


    public function isSatisfiedBy(DomainAggregateInterface $aggregate): bool
    {
        foreach ($this->specifications as $specification) {
            $isSatisfied = $specification->isSatisfiedBy($aggregate);

            if (
                $this->type === self::TYPE_AND &&
                !$isSatisfied
            ) {
                return false;
            }

            if (
                $this->type === self::TYPE_OR &&
                $isSatisfied
            ) {
                return true;
            }
        }

        return $this->type === self::TYPE_AND;
    }

    public function toExpression(): ExpressionInterface
    {
        $expressions = [];
        foreach ($this->specifications as $specification) {
            $expressions[] = $specification->toExpression();
        }

        $typeMap = [
            self::TYPE_AND => CompositeExpression::TYPE_AND,
            self::TYPE_OR => CompositeExpression::TYPE_OR,
        ];

        return new CompositeExpression(
            $typeMap[$this->type],
            $expressions
        );

    }
}