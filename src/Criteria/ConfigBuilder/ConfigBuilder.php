<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ConfigBuilder;

use Ergnuor\DomainModel\Criteria\Config\Field;
use Ergnuor\DomainModel\Criteria\Config\Field\Filter;
use Ergnuor\DomainModel\Criteria\Config\Field\Sorting;
use Ergnuor\DomainModel\Criteria\ConfigBuilder\Sorting\SortingConfigNormalizer;
use Ergnuor\DomainModel\Criteria\ValueSource\ExpressionValueSource;
use Ergnuor\DomainModel\Criteria\ValueSource\FieldValueSource;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceTypeEnum;
use Ergnuor\DomainModel\Criteria\Config\Config;
use Ergnuor\DomainModel\Criteria\ConfigBuilder\Filter\FilterConfigNormalizer;

class ConfigBuilder
{
    public function build(array $arrayConfig): Config
    {
//        $arrayConfig = [
//            'id' => [
//                'filter' => 'u.id',
//                'sorting' => 'u.id',
//            ],
//
//            'name' => [
//                'filter' => '@mapper',
//                'sorting' => 'u.id + 1',
//            ],
//
//            'field' => [
//                'filter' => '@' . CrmUserFieldExpressionMapper::class,
//                'sorting' => 'u.field',
//            ],
//
//            'id_new' => [
//                'filter' => [
//                    'field' => 'u.id',
//                ]
//            ],
//
//            'id_expression' => [
//                'filter' => [
//                    'expression' => 'u.id + 1',
//                ]
//            ],
//
//            'name_new' => [
//                'filter' => [
//                    'mapper' => CrmUserFieldExpressionMapper::class,
//                ],
//                'sorting' => [
//                    'field' => 'u.name',
//                ]
//            ],
//
//            'full_config' => [
//                'filter' => [
//                    'valueSource' => [
//                        'type' => ValueSourceTypeEnum::FIELD,
//                        'field' => 'u.id',
//                    ],
//                    'mapper' => 'mapperServiceId',
//                ],
//                'sorting' => [
//                    'field' => 'u.name',
//                ],
//            ],
//
//            'full_config2' => [
//                'filter' => [
//                    'valueSource' => [
//                        'type' => ValueSourceTypeEnum::EXPRESSION,
//                        'expression' => 'u.id + 1',
//                    ],
//                    'mapper' => new CrmUserFieldExpressionMapper(),
//                ],
//            ],
//        ];

        $config = new Config();

        foreach ($arrayConfig as $fieldName => $fieldConfig) {
            $filter = null;
            if (array_key_exists('filter', $fieldConfig)) {
                $filterConfig = FilterConfigNormalizer::normalize($fieldName, $fieldConfig['filter']);
                $filter = $this->makeFilter($filterConfig);
            }

            $sorting = null;
            if (array_key_exists('sorting', $fieldConfig)) {
                $sortingConfig = SortingConfigNormalizer::normalize($fieldName, $fieldConfig['sorting']);
                $sorting = $this->makeSorting($sortingConfig);
            }

            $field = new Field(
                $fieldName,
                $filter,
                $sorting
            );

            $config->addField($field);
        }

        return $config;
    }

    private function makeFilter(array $filterConfig): Filter
    {
        $valueSource = null;
        if (isset($filterConfig['valueSource'])) {
            $valueSource = $this->makeValueSource($filterConfig['valueSource']);
        }

        return new Filter(
            $valueSource,
            $filterConfig['mapper'] ?? null,
        );
    }

    private function makeValueSource(array $valueSourceArrayConfig): ?ValueSourceInterface
    {
        return match ($valueSourceArrayConfig['type']) {
            ValueSourceTypeEnum::FIELD => new FieldValueSource((string)$valueSourceArrayConfig['field']),
            ValueSourceTypeEnum::EXPRESSION => new ExpressionValueSource((string)$valueSourceArrayConfig['expression']),
            default => throw new \RuntimeException(
                sprintf(
                    "Unknown value source type '%s'",
                    get_debug_type($valueSourceArrayConfig['type'])
                )
            ),
        };
    }

    private function makeSorting(array $sortingConfig): Sorting
    {
        return new Sorting(
            $this->makeValueSource($sortingConfig['valueSource'])
        );
    }
}