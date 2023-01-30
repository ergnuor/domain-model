<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ConfigBuilder\Filter;

use Ergnuor\DomainModel\Criteria\ConfigBuilder\ValueSourceNormalizer;
use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceTypeEnum;

class FilterConfigNormalizer
{
    public static function normalize(string $fieldName, null|string|array|FieldExpressionMapperInterface $config): array
    {
        $normalizedConfig = self::makeInitialNormalizedConfig($config, $fieldName);

        if (isset($normalizedConfig['valueSource'])) {
            $normalizedConfig['valueSource'] = ValueSourceNormalizer::normalize($normalizedConfig['valueSource']);
        }

        return $normalizedConfig;
    }

    private static function makeInitialNormalizedConfig(
        null|array|string|FieldExpressionMapperInterface $config,
        string $fieldName
    ): array {
        return match (true) {
            $config === null => self::expandNullValueFilter($fieldName),
            is_string($config) => self::expandStringValueFilter($config),
            $config instanceof FieldExpressionMapperInterface => self::expandFieldMapperInterfaceValueFilter($config),
            default => $config,
        };
    }

    private static function expandNullValueFilter(string $fieldName): array
    {
        return [
            'valueSource' => [
                'type' => ValueSourceTypeEnum::FIELD,
                'field' => $fieldName,
            ],
        ];
    }

    private static function expandStringValueFilter(string $config): array
    {
        if (str_starts_with($config, '@')) {
            return [
                'mapper' => substr($config, 1),
            ];
        }

        return [
            'valueSource' => [
                'type' => ValueSourceTypeEnum::FIELD,
                'field' => $config,
            ],
        ];
    }

    private static function expandFieldMapperInterfaceValueFilter(FieldExpressionMapperInterface $fieldMapper): array
    {
        return [
            'mapper' => $fieldMapper,
        ];
    }
}