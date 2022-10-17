<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Specification;

class SpecificationBuilder
{
    /**
     * @param SpecificationInterface ...$specifications
     * @return CompositeSpecification
     */
    public static function and(...$specifications): CompositeSpecification
    {
        return new CompositeSpecification(CompositeSpecification::TYPE_AND, func_get_args());
    }

    /**
     * @param SpecificationInterface ...$specifications
     * @return CompositeSpecification
     */
    public static function or(...$specifications): CompositeSpecification
    {
        return new CompositeSpecification(CompositeSpecification::TYPE_OR, func_get_args());
    }

    public static function not(SpecificationInterface $specification): NegationSpecification
    {
        return new NegationSpecification($specification);
    }
}