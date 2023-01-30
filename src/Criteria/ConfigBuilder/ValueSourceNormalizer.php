<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ConfigBuilder;

use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceTypeEnum;

class ValueSourceNormalizer
{
    public static function normalize($config): array
    {
        if (is_string($config)) {
            $normalizedConfig = [
                'type' => ValueSourceTypeEnum::FIELD,
                'field' => $config,
            ];
        } else {
            $normalizedConfig = $config;
        }

        if (!is_array($normalizedConfig)) {
            throw new \RuntimeException(
                sprintf(
                    "Value source config can be string or array. Got '%s'",
                    get_debug_type($normalizedConfig)
                )
            );
        }

        if (!array_key_exists('type', $normalizedConfig)) {
            $normalizedConfig['type'] = self::inferType($normalizedConfig);
        }

        if (!($normalizedConfig['type'] instanceof ValueSourceTypeEnum)) {
            throw new \UnexpectedValueException(
                sprintf(
                    "Unexpected value source type. '%s' expecting",
                    ValueSourceTypeEnum::class
                )
            );
        }

        return $normalizedConfig;
    }

    private static function inferType($normalizedConfig): ValueSourceTypeEnum
    {
        if (isset($normalizedConfig['field'])) {
            return ValueSourceTypeEnum::FIELD;
        }

        if (isset($normalizedConfig['expression'])) {
            return ValueSourceTypeEnum::EXPRESSION;
        }

        throw new \RuntimeException("Can not infer value source config type");
    }
}