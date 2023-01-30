<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\CommonParameterMapper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types as DoctrineTypes;
use Ergnuor\DomainModel\Criteria\Type\Types;

class DoctrineParameterTypeMap
{
    private static array $typeMap = [
        Types::ARRAY => DoctrineTypes::JSON,
        Types::INT_ARRAY => Connection::PARAM_INT_ARRAY,
        Types::STR_ARRAY => Connection::PARAM_STR_ARRAY,
        Types::ASCII_STRING => DoctrineTypes::ASCII_STRING,
        Types::BIGINT => DoctrineTypes::BIGINT,
        Types::BINARY => DoctrineTypes::BINARY,
        Types::BLOB => DoctrineTypes::BLOB,
        Types::BOOLEAN => DoctrineTypes::BOOLEAN,
        Types::DATE_MUTABLE => DoctrineTypes::DATE_MUTABLE,
        Types::DATE_IMMUTABLE => DoctrineTypes::DATE_IMMUTABLE,
        Types::DATEINTERVAL => DoctrineTypes::DATEINTERVAL,
        Types::DATETIME_MUTABLE => DoctrineTypes::DATETIME_MUTABLE,
        Types::DATETIME_IMMUTABLE => DoctrineTypes::DATETIME_IMMUTABLE,
        Types::DATETIMETZ_MUTABLE => DoctrineTypes::DATETIMETZ_MUTABLE,
        Types::DATETIMETZ_IMMUTABLE => DoctrineTypes::DATETIMETZ_IMMUTABLE,
        Types::DECIMAL => DoctrineTypes::DECIMAL,
        Types::FLOAT => DoctrineTypes::FLOAT,
        Types::GUID => DoctrineTypes::GUID,
        Types::INTEGER => DoctrineTypes::INTEGER,
        Types::JSON => DoctrineTypes::JSON,
        Types::OBJECT => DoctrineTypes::JSON,
        Types::SIMPLE_ARRAY => DoctrineTypes::SIMPLE_ARRAY,
        Types::SMALLINT => DoctrineTypes::SMALLINT,
        Types::STRING => DoctrineTypes::STRING,
        Types::TEXT => DoctrineTypes::TEXT,
        Types::TIME_MUTABLE => DoctrineTypes::TIME_MUTABLE,
        Types::TIME_IMMUTABLE => DoctrineTypes::TIME_IMMUTABLE,
    ];

    public static function getMappedType(string $parameterType): string|int|null
    {
        return self::$typeMap[$parameterType] ?? null;
    }
}