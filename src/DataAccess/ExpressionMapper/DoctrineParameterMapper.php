<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Parameter as DoctrineParameter;
use Doctrine\DBAL\Types\Types as DoctrineTypes;

class DoctrineParameterMapper implements ParameterMapperInterface
{
    private array $typeMap = [
        ParameterTypes::ARRAY => DoctrineTypes::ARRAY,
        ParameterTypes::INT_ARRAY => Connection::PARAM_INT_ARRAY,
        ParameterTypes::STR_ARRAY => Connection::PARAM_STR_ARRAY,
        ParameterTypes::ASCII_STRING => DoctrineTypes::ASCII_STRING,
        ParameterTypes::BIGINT => DoctrineTypes::BIGINT,
        ParameterTypes::BINARY => DoctrineTypes::BINARY,
        ParameterTypes::BLOB => DoctrineTypes::BLOB,
        ParameterTypes::BOOLEAN => DoctrineTypes::BOOLEAN,
        ParameterTypes::DATE_MUTABLE => DoctrineTypes::DATE_MUTABLE,
        ParameterTypes::DATE_IMMUTABLE => DoctrineTypes::DATE_IMMUTABLE,
        ParameterTypes::DATEINTERVAL => DoctrineTypes::DATEINTERVAL,
        ParameterTypes::DATETIME_MUTABLE => DoctrineTypes::DATETIME_MUTABLE,
        ParameterTypes::DATETIME_IMMUTABLE => DoctrineTypes::DATETIME_IMMUTABLE,
        ParameterTypes::DATETIMETZ_MUTABLE => DoctrineTypes::DATETIMETZ_MUTABLE,
        ParameterTypes::DATETIMETZ_IMMUTABLE => DoctrineTypes::DATETIMETZ_IMMUTABLE,
        ParameterTypes::DECIMAL => DoctrineTypes::DECIMAL,
        ParameterTypes::FLOAT => DoctrineTypes::FLOAT,
        ParameterTypes::GUID => DoctrineTypes::GUID,
        ParameterTypes::INTEGER => DoctrineTypes::INTEGER,
        ParameterTypes::JSON => DoctrineTypes::JSON,
        ParameterTypes::OBJECT => DoctrineTypes::OBJECT,
        ParameterTypes::SIMPLE_ARRAY => DoctrineTypes::SIMPLE_ARRAY,
        ParameterTypes::SMALLINT => DoctrineTypes::SMALLINT,
        ParameterTypes::STRING => DoctrineTypes::STRING,
        ParameterTypes::TEXT => DoctrineTypes::TEXT,
        ParameterTypes::TIME_MUTABLE => DoctrineTypes::TIME_MUTABLE,
        ParameterTypes::TIME_IMMUTABLE => DoctrineTypes::TIME_IMMUTABLE,
    ];

    /**
     * @param Parameter[] $parameters
     * @return mixed
     */
    public function mapParameters(array $parameters): ArrayCollection
    {
        $collection = new ArrayCollection();


        foreach ($parameters as $parameter) {
            $collection->add(
                new DoctrineParameter(
                    $parameter->getName(),
                    $parameter->getValue(),
                    $this->getMappedType($parameter->getType())
                )
            );
        }

        return $collection;
    }

    private function getMappedType(string $parameterType): string|int|null
    {
        return $this->typeMap[$parameterType] ?? null;
    }
}