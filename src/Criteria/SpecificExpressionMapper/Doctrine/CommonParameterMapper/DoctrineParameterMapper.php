<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\CommonParameterMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter as DoctrineParameter;
use Ergnuor\DomainModel\Criteria\Parameter;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ParameterMapperInterface;

class DoctrineParameterMapper implements ParameterMapperInterface
{
    /**
     * @param ArrayCollection<int, Parameter> $parameters
     * @return mixed
     */
    public function mapParameters(ArrayCollection $parameters): ArrayCollection
    {
        return $parameters->map(
            fn(Parameter $parameter) => new DoctrineParameter(
                $parameter->getName(),
                $parameter->getValue(),
                DoctrineParameterTypeMap::getMappedType($parameter->getType())
            )
        );
    }
}