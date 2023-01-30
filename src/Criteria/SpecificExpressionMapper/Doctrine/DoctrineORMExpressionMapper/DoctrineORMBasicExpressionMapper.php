<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\SpecificExpressionMapper\Doctrine\DoctrineORMExpressionMapper;

use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Doctrine\ORM\Query\Expr;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionContext;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\FieldMapResult;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\Identifiers;
use Ergnuor\DomainModel\Criteria\FieldMapper\FieldExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\Parameter;

class DoctrineORMBasicExpressionMapper implements FieldExpressionMapperInterface
{
    private static array $operatorMap = [
        Expression::EQ => Expr\Comparison::EQ,
        Expression::NOT_EQ => Expr\Comparison::NEQ,
        Expression::GT => Expr\Comparison::GT,
        Expression::GTE => Expr\Comparison::GTE,
        Expression::LT => Expr\Comparison::LT,
        Expression::LTE => Expr\Comparison::LTE
    ];

    private Expr $expr;

    public function __construct()
    {
        $this->expr = new Expr();
    }

    public function mapExpression(
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): ?FieldMapResult {
        $valueSource = $expressionContext->getValueSource();

        if (!($valueSource instanceof \Stringable)) {
            throw new \RuntimeException(
                sprintf(
                    "'%s' value source expected",
                    \Stringable::class,
                )
            );
        }

        $fieldExpression = (string)$valueSource;


        $operator = $expressionContext->getOperator();

        return match (true) {
            (
                (
                    $operator === Expression::EQ ||
                    $operator === Expression::NOT_EQ
                ) &&
                $expressionContext->getValue() === null
            ) => $this->makeNullExpression($fieldExpression, $operator === Expression::NOT_EQ),

            (
                $operator === Expression::IN ||
                $operator === Expression::NOT_IN
            ) => $this->makeInExpression(
                $fieldExpression,
                $expressionContext,
                $identifiers
            ),

            (
                $operator === Expression::LIKE ||
                $operator === Expression::NOT_LIKE
            ) => $this->makeLikeExpression(
                $fieldExpression,
                $expressionContext,
                $identifiers
            ),

            default => $this->makeComparisonExpression($fieldExpression, $expressionContext, $operator, $identifiers),
        };
    }

    private function makeNullExpression(string $fieldExpression, bool $isNot): ?FieldMapResult
    {
        if ($isNot) {
            return new FieldMapResult(
                $this->expr->isNotNull($fieldExpression)
            );
        }

        return new FieldMapResult(
            $this->expr->isNull($fieldExpression)
        );
    }

    private function makeInExpression(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): FieldMapResult {
        $value = (array)$expressionContext->getValue();

//        dump([
//            $value,
//            (string)(new Expr\Func($expressionContext->getRealFieldName() . ' IN', ['NULL'])),
//            (string)(new Expr\Literal('asdf')),
//        ]);
//
//        if (empty($value)) {
//            return new ExpressionMapResult(
////                new Expr\Func($expressionContext->getRealFieldName() . ' IN', ['NULL'])
////                new Expr\Func($expressionContext->getRealFieldName() . ' IN', ['NULL'])
////                $this->expr->in($expressionContext->getRealFieldName(), new Expr\Literal('NULL'))
////                $this->expr->in($expressionContext->getRealFieldName(), ['NULL'])
//                $this->expr->in($expressionContext->getRealFieldName(), [1])
////                $this->expr->in($expressionContext->getRealFieldName(), [])
////                $expressionContext->getRealFieldName() . " IN (NULL)"
////                $expressionContext->getRealFieldName() . " IN (SELECT 1=0)"
//            );
//        }

        [$notNullValues, $nullValues] = $this->splitNullAndNotNullValues($value);

        if (
            empty($value) ||
            empty($nullValues)
        ) {
            return $this->makeNormalInExpression(
                $fieldExpression,
                $expressionContext->withValue($notNullValues),
                $identifiers
            );
        }

        if (empty($notNullValues)) {
            return $this->makeNullExpression(
                $fieldExpression,
                $expressionContext->getOperator() === Expression::NOT_IN
            );
        }

        return $this->makeInExpressionWithNullValues(
            $fieldExpression,
            $expressionContext->withValue($notNullValues),
            $identifiers
        );
    }

    private function splitNullAndNotNullValues(array $values): array
    {
        $notNullValues = array_diff($values, [null]);
        $nullValues = array_diff($values, $notNullValues);

        return [$notNullValues, $nullValues];
    }

    private function makeNormalInExpression(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): FieldMapResult {
        return $this->makeSingleParameterExpression(
            $fieldExpression,
            $expressionContext,
            $identifiers,
            function ($fieldExpression, ExpressionContext $expressionContext, string $placeholder) {
                if ($expressionContext->getOperator() === Expression::NOT_IN) {
                    return $this->expr->notIn($fieldExpression, $placeholder);
                }

                return $this->expr->in($fieldExpression, $placeholder);
            }
        );
    }

    private function makeSingleParameterExpression(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        Identifiers $identifiers,
        callable $expressionGetter
    ): FieldMapResult {
        $parameterName = $identifiers->getNext($expressionContext->getFieldName());
        $placeholder = $this->makePlaceholder($parameterName);

        $expressionResult = new FieldMapResult($expressionGetter(
            $fieldExpression,
            $expressionContext,
            $placeholder
        ));

        $expressionResult->addParameter(
            new Parameter($parameterName, $expressionContext->getValue(), $expressionContext->getType())
        );

        return $expressionResult;
    }

    private function makePlaceholder(string $name): string
    {
        return ':' . $name;
    }

    private function makeInExpressionWithNullValues(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): FieldMapResult {
        return $this->makeSingleParameterExpression(
            $fieldExpression,
            $expressionContext,
            $identifiers,
            function ($fieldExpression, ExpressionContext $expressionContext, string $placeholder) {
                if ($expressionContext->getOperator() === Expression::NOT_IN) {
                    return $this->expr->orX(
                        $this->expr->isNotNull($fieldExpression),
                        $this->expr->notIn($fieldExpression, $placeholder)
                    );
                }

                return $this->expr->orX(
                    $this->expr->isNull($fieldExpression),
                    $this->expr->in($fieldExpression, $placeholder)
                );
            }
        );
    }

    private function makeLikeExpression(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        Identifiers $identifiers
    ): FieldMapResult {
        return $this->makeSingleParameterExpression(
            $fieldExpression,
            $expressionContext,
            $identifiers,
            function ($fieldExpression, ExpressionContext $expressionContext, string $placeholder) {
                if ($expressionContext->getOperator() === Expression::NOT_LIKE) {
                    return $this->expr->notLike($fieldExpression, $placeholder);
                }

                return $this->expr->like($fieldExpression, $placeholder);
            }
        );
    }

    private function makeComparisonExpression(
        string $fieldExpression,
        ExpressionContext $expressionContext,
        string $operator,
        Identifiers $identifiers
    ): ?FieldMapResult {
        $operator = $this->getDoctrineComparisonOperator($operator);

        if ($operator === null) {
            return null;
        }

        return $this->makeSingleParameterExpression(
            $fieldExpression,
            $expressionContext,
            $identifiers,
            function ($fieldExpression, ExpressionContext $expressionContext, string $placeholder) use ($operator) {
                return new Expr\Comparison(
                    $fieldExpression,
                    $operator,
                    $placeholder
                );
            }
        );
    }

    private function getDoctrineComparisonOperator(string $operator): ?string
    {
        if (!isset(self::$operatorMap[$operator])) {
            return null;
        }

        return self::$operatorMap[$operator];
    }
}