<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Type;

use BackedEnum;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

use function current;
use function is_array;
use function is_bool;
use function is_int;

class TypeInferer
{
    public static function inferType($value): string
    {
        if (is_int($value)) {
            return Types::INTEGER;
        }

        if (is_bool($value)) {
            return Types::BOOLEAN;
        }

        if ($value instanceof DateTimeImmutable) {
            return Types::DATETIME_IMMUTABLE;
        }

        if ($value instanceof DateTimeInterface) {
            return Types::DATETIME_MUTABLE;
        }

        if ($value instanceof DateInterval) {
            return Types::DATEINTERVAL;
        }

        if ($value instanceof BackedEnum) {
            return is_int($value->value)
                ? Types::INTEGER
                : Types::STRING;
        }

        if (is_array($value)) {
            $firstValue = current($value);
            if ($firstValue instanceof BackedEnum) {
                $firstValue = $firstValue->value;
            }

            return is_int($firstValue)
                ? Types::INT_ARRAY
                : Types::STR_ARRAY;
        }

        return Types::STRING;
    }
}
