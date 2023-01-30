<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Ergnuor\DomainModel\Criteria\Parameter;

interface ParameterMapperInterface
{
    /**
     * @param ArrayCollection<int, \Ergnuor\DomainModel\Criteria\Parameter> $parameters
     * @return mixed
     */
    public function mapParameters(ArrayCollection $parameters): mixed;
}