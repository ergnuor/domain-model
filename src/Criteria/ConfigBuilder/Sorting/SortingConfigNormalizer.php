<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ConfigBuilder\Sorting;

use Ergnuor\DomainModel\Criteria\ConfigBuilder\ValueSourceNormalizer;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceTypeEnum;

class SortingConfigNormalizer
{
    public static function normalize(string $fieldName, null|string|array $config): array
    {
        $normalizedConfig = self::makeInitialNormalizedConfig($config, $fieldName);

        if (!isset($normalizedConfig['valueSource'])) {
            throw new \RuntimeException("Value source expected for sorting config");
        }

        $normalizedConfig['valueSource'] = ValueSourceNormalizer::normalize($normalizedConfig['valueSource']);

        return $normalizedConfig;
    }

    private static function makeInitialNormalizedConfig(
        array|string|null $config,
        string $fieldName
    ): array {
        return match (true) {
            $config === null => self::expandNullValueFilter($fieldName),
            is_string($config) => self::expandStringValueFilter($config),
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
        return [
            'valueSource' => [
                'type' => ValueSourceTypeEnum::FIELD,
                'field' => $config,
            ],
        ];
    }
}