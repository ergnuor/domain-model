<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;
use Ergnuor\DomainModel\DataAccess\Expression\CompositeExpression;
use Ergnuor\DomainModel\DataAccess\Expression\NegationExpression;
use Ergnuor\DomainModel\DataAccess\Expression\Value;
use RuntimeException;

/**
 * Класс преобразующий фильтры по полям доменных сущностей в вид, понятный нижележащему уровню
 */
class ExpressionMapper extends AbstractCommonExpressionMapper
{
    protected array $fieldMap = [];

    /** @var Parameter[] */
    private array $parameters = [];
    private int $aliasCount = 0;
    private ParameterMapperInterface $parameterTransformer;
    /** @var CustomExpressionMapperInterface[] */
    private array $customExpressionMappers = [];
    private BasicExpressionMapperInterface $basicExpressionMapper;

    public function __construct(
        ParameterMapperInterface $parameterTransformer,
        BasicExpressionMapperInterface $basicExpressionMapper,
        ?array $fieldMap = null,
    ) {
        $this->parameterTransformer = $parameterTransformer;
        $this->basicExpressionMapper = $basicExpressionMapper;
        $this->fieldMap = $fieldMap;
    }

    public function addCustomExpressionMapper(CustomExpressionMapperInterface $mapper)
    {
        $this->customExpressionMappers[] = $mapper;
    }

    public function getNextAliasName(string $originalName): string
    {
        return $originalName . '_' . ($this->aliasCount++);
    }

    public function getMappedParameters(): mixed
    {
        return $this->parameterTransformer->mapParameters($this->parameters);
    }

    public function clearParameters(): void
    {
        $this->parameters = [];
    }

    /**
     * {@inheritDoc}
     */
    public function walkCompositeExpression(CompositeExpression $compositeExpression): mixed
    {
        $expressionList = [];

        foreach ($compositeExpression->getExpressionList() as $child) {
            $expressionList[] = $this->dispatch($child);
        }

        $type = $compositeExpression->getType();
        switch ($type) {
            case CompositeExpression::TYPE_AND:
                return $this->basicExpressionMapper->andX(...$expressionList);

            case CompositeExpression::TYPE_OR:
                return $this->basicExpressionMapper->orX(...$expressionList);

            default:
                throw new RuntimeException("Unknown composite " . $type);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function walkComparisonExpression(ComparisonExpression $comparison): mixed
    {
        $parameterName = str_replace('.', '_', $comparison->getField()) . '_' . count($this->parameters);
        $parameter = new Parameter($parameterName, $this->walkValue($comparison->getValue()));
        $placeholder = ':' . $parameterName;

        if (
            $this->fieldMap !== null &&
            isset($this->fieldMap[$comparison->getField()])
        ) {
            return $this->getBasicExpression(
                $comparison,
                $parameter,
                $this->fieldMap[$comparison->getField()],
                $placeholder
            );
        }

        $expression = $this->getCustomExpression($comparison, $parameter, $placeholder);
        if ($expression !== null) {
            return $expression;
        }

        if ($this->fieldMap === null) {
            return $this->getBasicExpression(
                $comparison,
                $parameter,
                $comparison->getField(),
                $placeholder
            );
        }

        throw new \RuntimeException("Can not get expression for field '{$comparison->getField()}'");
    }

    public function walkNegationExpression(NegationExpression $negation): mixed
    {
        return $this->basicExpressionMapper->not($this->dispatch($negation->getExpression()));
    }

    /**
     * {@inheritDoc}
     */
    public function walkValue(Value $value): mixed
    {
        return $value->getValue();
    }

    /**
     * Позволяет реализовать фильтрацию по полям не входящим в состав сущности, либо имеющих сложную природу.
     * Например, мы можем создать поле для фильтрации 'isActive', которое в реальности будет означать что-нибудь вроде
     * "Авторизовывался в системе за последние сутки" и использовать его при фильтрации следующим образом:
     * $anyRepository->findBy([
     *      'isActive' => true',
     * ]);
     * Пример реализации: {@see \App\Infrastructure\_Repository\Crm\ExpressionMapper\Example\UserExpressionMapper}
     *
     * @param ComparisonExpression $comparison
     * @param Parameter $parameter
     * @param string $placeholder
     * @return mixed|null
     */
    final protected function getCustomExpression(
        ComparisonExpression $comparison,
        Parameter $parameter,
        string $placeholder
    ): mixed {
        foreach ($this->customExpressionMappers as $mapper) {
            $expression = $mapper->getExpression($comparison, $parameter, $placeholder, $this);
            if ($expression !== null) {
                return $expression;
            }
        }

        return null;
    }

    /**
     * @param ComparisonExpression $comparison
     * @param Parameter $parameter
     * @param string $field
     * @param string $placeholder
     * @return mixed
     */
    public function getBasicExpression(
        ComparisonExpression $comparison,
        Parameter $parameter,
        string $field,
        string $placeholder
    ): mixed {
        switch ($comparison->getOperator()) {
            case ComparisonExpression::IN:
            case ComparisonExpression::NIN:
                $value = (array)$parameter->getValue();

                $hasNullValues = count(
                        array_filter(
                            $value,
                            fn($v) => is_null($v)
//                            function ($v) {
//                                return is_null($v);
//                            }
                        )
                    ) > 0;

                $notNullValues = array_diff($value, [null]);

                $parameter->setValue($notNullValues);
                $this->addParameter($parameter);

                if ($comparison->getOperator() == ComparisonExpression::NIN) {
                    if ($hasNullValues) {
                        return $this->basicExpressionMapper->orX(
                            $this->basicExpressionMapper->notIn($field, $placeholder),
                            $this->basicExpressionMapper->isNotNull($field),
                        );
                    } else {
                        return $this->basicExpressionMapper->notIn($field, $placeholder);
                    }
                }

                if ($hasNullValues) {
                    return $this->basicExpressionMapper->orX(
                        $this->basicExpressionMapper->in($field, $placeholder),
                        $this->basicExpressionMapper->isNull($field),
                    );
                } else {
                    return $this->basicExpressionMapper->in($field, $placeholder);
                }


            case ComparisonExpression::EQ:
            case ComparisonExpression::IS:
                if ($parameter->getValue() === null) {
                    return $this->basicExpressionMapper->isNull($field);
                }
                $this->addParameter($parameter);

                return $this->basicExpressionMapper->eq($field, $placeholder);
            case ComparisonExpression::NEQ:
                if ($parameter->getValue() === null) {
                    return $this->basicExpressionMapper->isNotNull($field);
                }
                $this->addParameter($parameter);

                return $this->basicExpressionMapper->neq($field, $placeholder);
            case ComparisonExpression::CONTAINS:
            case ComparisonExpression::NCONTAINS:
                $parameter->setValue('%' . $parameter->getValue() . '%', $parameter->getType());
                $this->addParameter($parameter);

                if ($comparison->getOperator() == ComparisonExpression::CONTAINS) {
                    return $this->basicExpressionMapper->like($field, $placeholder);
                }
                return $this->basicExpressionMapper->notLike($field, $placeholder);
            case ComparisonExpression::MEMBER_OF:
                return $this->basicExpressionMapper->isMemberOf($comparison->getField(),
                    $comparison->getValue()->getValue());
            case ComparisonExpression::STARTS_WITH:
            case ComparisonExpression::NSTARTS_WITH:
                $parameter->setValue($parameter->getValue() . '%', $parameter->getType());
                $this->addParameter($parameter);

                if ($comparison->getOperator() == ComparisonExpression::STARTS_WITH) {
                    return $this->basicExpressionMapper->like($field, $placeholder);
                }
                return $this->basicExpressionMapper->notLike($field, $placeholder);
            case ComparisonExpression::ENDS_WITH:
            case ComparisonExpression::NENDS_WITH:
                $parameter->setValue('%' . $parameter->getValue(), $parameter->getType());
                $this->addParameter($parameter);

                if ($comparison->getOperator() == ComparisonExpression::ENDS_WITH) {
                    return $this->basicExpressionMapper->like($field, $placeholder);
                }
                return $this->basicExpressionMapper->notLike($field, $placeholder);
            default:
                $this->addParameter($parameter);
                return $this->basicExpressionMapper->comparison($field, $comparison->getOperator(), $placeholder);
        }
    }

    public function addParameter(Parameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }
}
