<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Ergnuor\DomainModel\Criteria\Exception\UnsupportedFieldOperatorException;
use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Ergnuor\DomainModel\Criteria\Expression\CompositeExpression;
use Ergnuor\DomainModel\Criteria\Expression\NegationExpression;
use Ergnuor\DomainModel\Criteria\Expression\Value;
use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\Parameter;
use Ergnuor\DomainModel\Criteria\Type\TypeInferer;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceBuilder;
use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ExpressionMapper extends AbstractExpressionMapper
{
    /** @var array<string, Field> */
    private array $fields = [];

    /** @var ArrayCollection<int, Parameter> */
    private ArrayCollection $parameters;
    private ParameterMapperInterface $parameterTransformer;
    private FieldExpressionMapperInterface $basicFieldExpressionMapper;
    private Identifiers $identifiers;
    private LogicalExpressionMapperInterface $logicalExpressionMapper;
    private ValueSourceBuilder $valueSourceBuilder;
    private ?ContainerInterface $expressionMapperContainer;

    public function __construct(
        ParameterMapperInterface $parameterTransformer,
        FieldExpressionMapperInterface $basicFieldExpressionMapper,
        LogicalExpressionMapperInterface $logicalExpressionMapper,
        ContainerInterface $expressionMapperContainer = null
    ) {
        $this->parameterTransformer = $parameterTransformer;
        $this->basicFieldExpressionMapper = $basicFieldExpressionMapper;
        $this->logicalExpressionMapper = $logicalExpressionMapper;
        $this->expressionMapperContainer = $expressionMapperContainer;

        $this->valueSourceBuilder = new ValueSourceBuilder();

        $this->reset();
    }

    public function reset(): void
    {
        $this->fields = [];
        $this->parameters = new ArrayCollection();
        $this->identifiers = new Identifiers();
    }

    public function addField(
        string $fieldName,
        ?ValueSourceInterface $valueSource,
        null|string|FieldExpressionMapperInterface $fieldExpressionMapper = null
    ): void {
        $valueSource = $valueSource ?? $this->valueSourceBuilder->field($fieldName);

        $this->fields[$fieldName] = new Field(
            $valueSource,
            $fieldExpressionMapper ?? $this->basicFieldExpressionMapper
        );
    }

    public function getMappedParameters(): mixed
    {
        return $this->parameterTransformer->mapParameters($this->parameters);
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
        return match ($type) {
            CompositeExpression::TYPE_AND => $this->logicalExpressionMapper->andX(...$expressionList),
            CompositeExpression::TYPE_OR => $this->logicalExpressionMapper->orX(...$expressionList),
            default => throw new RuntimeException(
                sprintf(
                    "Unknown composite type '%s'",
                    $type
                )
            ),
        };
    }

    /**
     * {@inheritDoc}
     */
    public function walkExpression(Expression $expression): mixed
    {
        $value = $this->walkValue($expression->getValue());

        $mapResult = $this->mapExpression($expression, $value);

        if ($mapResult === null) {
            throw UnsupportedFieldOperatorException::fromExpression($expression);
        }

        foreach ($mapResult->getParameters() as $parameter) {
            $this->addParameter($parameter);
        }

        return $mapResult->getMappedExpression();
    }

    /**
     * {@inheritDoc}
     */
    public function walkValue(Value $value): mixed
    {
        return $value->getValue();
    }

    private function mapExpression(Expression $expression, mixed $value): ?FieldMapResult
    {
        $field = $this->getField($expression->getFieldName());

        $expressionContext = new ExpressionContext(
            $value,
            TypeInferer::inferType($value),
            $expression->getFieldName(),
            $expression->getOperator(),
            $field->getValueSource(),
        );

        $fieldExpressionMapper = $this->getFieldExpressionMapper($field);

        return $fieldExpressionMapper->mapExpression($expressionContext, $this->identifiers);
    }

    private function getField(string $fieldName): Field
    {
        $field = $this->fields[$fieldName] ?? null;

        if ($field === null) {
            throw new \RuntimeException(
                sprintf(
                    "Unknown field '%s'",
                    $fieldName
                )
            );
        }

        return $field;
    }

    private function getFieldExpressionMapper(Field $field): FieldExpressionMapperInterface
    {
        $fieldExpressionMapper = $field->getFieldExpressionMapper();

//        dd([
//            $field,
//            $fieldExpressionMapper
//        ]);

        if (is_string($fieldExpressionMapper)) {
            if ($this->expressionMapperContainer === null) {
                throw new \RuntimeException(
                    sprintf(
                        "Unable to get field expression mapper with service name '%s': expression mapper service container is not defined",
                        $fieldExpressionMapper
                    )
                );
            }

            $fieldExpressionMapper = $this->expressionMapperContainer->get($fieldExpressionMapper);
        }

        return $fieldExpressionMapper;
    }

    private function addParameter(Parameter $parameter): void
    {
        $this->parameters->add($parameter);
    }

    public function walkNegationExpression(NegationExpression $negation): mixed
    {
        return $this->logicalExpressionMapper->not($this->dispatch($negation->getExpression()));
    }
}
