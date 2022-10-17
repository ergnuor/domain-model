<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Specification;

abstract class AbstractSpecification implements SpecificationInterface
{
    public function and(...$specifications): CompositeSpecification
    {
        return $this->createCompositeSpecification(CompositeSpecification::TYPE_AND, func_get_args());
    }

    public function or(...$specifications): CompositeSpecification
    {
        return $this->createCompositeSpecification(CompositeSpecification::TYPE_OR, func_get_args());
    }

    private function createCompositeSpecification(
        string $type,
        array $specifications
    ): CompositeSpecification {
        return new CompositeSpecification(
            $type,
            array_merge(
                [
                    $this,
                ],
                $specifications
            )
        );
    }

    protected function createExpressionFromArray(array $arrayExpression): \Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface
    {
        $expressionBuilder = new \Ergnuor\DomainModel\DataAccess\ExpressionBuilder\FromArray();
        return $expressionBuilder->create($arrayExpression);
    }
}