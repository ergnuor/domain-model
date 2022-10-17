<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

use function current;
use function is_array;
use function is_bool;
use function is_int;

class ParameterTypeInferer
{
    public static function inferType($value): string
    {
        if (is_int($value)) {
            return ParameterTypes::INTEGER;
        }

        if (is_bool($value)) {
            return ParameterTypes::BOOLEAN;
        }

        if ($value instanceof DateTimeImmutable) {
            return ParameterTypes::DATETIME_IMMUTABLE;
        }

        if ($value instanceof DateTimeInterface) {
            return ParameterTypes::DATETIME_MUTABLE;
        }

        if ($value instanceof DateInterval) {
            return ParameterTypes::DATEINTERVAL;
        }

        if (is_array($value)) {
            return is_int(current($value))
                ? ParameterTypes::INT_ARRAY
                : ParameterTypes::STR_ARRAY;
        }

        return ParameterTypes::STRING;
    }
}
