<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

interface ParameterMapperInterface
{
    /**
     * @param Parameter[] $parameters
     * @return mixed
     */
    public function mapParameters(array $parameters): mixed;
}