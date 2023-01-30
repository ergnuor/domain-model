<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionHelper;

use Ergnuor\DomainModel\Criteria\Expression\Expression;
use Ergnuor\DomainModel\Criteria\Expression\CompositeExpression;
use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\Expression\NegationExpression;
use Ergnuor\DomainModel\Criteria\Expression\Value;
use Ergnuor\DomainModel\Criteria\ExpressionBuilder as expr;
use RuntimeException;

class FromArrayExpressionBuilder
{
    private const EQ = '=';

    private const LT = '<';
    private const LTE = '<=';
    private const GT = '>';
    private const GTE = '>=';

    private const IN = 'in';

    private const LIKE = 'like';
    private static array $suffixToExpressionOperatorMap = [
        self::EQ => Expression::EQ,

        self::LT => Expression::LT,
        self::LTE => Expression::LTE,
        self::GT => Expression::GT,
        self::GTE => Expression::GTE,

        self::IN => Expression::IN,

        self::LIKE => Expression::LIKE,
    ];
    private static array $oppositeOperatorMap = [
        Expression::EQ => Expression::NOT_EQ,
        Expression::NOT_EQ => Expression::EQ,

        Expression::IN => Expression::NOT_IN,
        Expression::NOT_IN => Expression::IN,

        Expression::GT => Expression::LTE,
        Expression::GTE => Expression::LT,

        Expression::LT => Expression::GTE,
        Expression::LTE => Expression::GT,

        Expression::LIKE => Expression::NOT_LIKE,
        Expression::NOT_LIKE => Expression::LIKE,
    ];

    public function build(?array $arrayExpression = null): ?ExpressionInterface
    {
        return $this->doBuild($arrayExpression, CompositeExpression::TYPE_AND);
    }

    protected function doBuild(array $arrayExpression, $type): CompositeExpression
    {
        $expressionList = [];

        foreach ($arrayExpression as $encodedFieldName => $value) {
            if (preg_match('/^(?P<inverted>!?)@(?P<operator>AND|OR).*/i', $encodedFieldName, $m)) {
                $nextType = strtolower($m['operator']) == 'and' ? CompositeExpression::TYPE_AND : CompositeExpression::TYPE_OR;

                $groupExpression = $this->doBuild($value, $nextType);

                if ($m['inverted']) {
                    $groupExpression = new NegationExpression($groupExpression);
                }

                $expressionList[] = $groupExpression;
            } else {
                $fieldName = FieldName::fromEncodedFieldName($encodedFieldName);

                if (!$fieldName->isHasSuffix()) {
                    if (is_array($value)) {
                        $expressionList[] = $this->createOperator($fieldName, self::IN, $value);
                    } else {
                        $expressionList[] = $this->createOperator($fieldName, self::EQ, $value);
                    }
                } else {
                    $expressionList[] = $this->createOperator($fieldName, $fieldName->getSuffix(), $value);
                }
            }
        }

        if ($type == CompositeExpression::TYPE_AND) {
            return expr::andX(...$expressionList);
        }

        return expr::orX(...$expressionList);
    }

    private function createOperator(FieldName $fieldName, string $suffix, $value): Expression
    {
        $expressionOperator = $this->getExpressionOperatorForSuffix($suffix);

        if ($fieldName->isInverted()) {
            $expressionOperator = $this->getOppositeExpressionOperator($expressionOperator);
        }

        return new Expression(
            $fieldName->getName(),
            $expressionOperator,
            new Value($value),
        );
    }

    private function getExpressionOperatorForSuffix(string $suffix): string
    {
        if (!isset(self::$suffixToExpressionOperatorMap[$suffix])) {
            throw new RuntimeException(sprintf(
                "Unknown suffix '%s'",
                $suffix,
            ));
        }

        return self::$suffixToExpressionOperatorMap[$suffix];
    }

    private function getOppositeExpressionOperator(string $operator): string
    {
        if (!isset(self::$oppositeOperatorMap[$operator])) {
            throw new RuntimeException(sprintf(
                "Unknown opposite operator for expression operator '%s'",
                $operator,
            ));
        }

        return self::$oppositeOperatorMap[$operator];
    }
}
