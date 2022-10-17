<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionBuilder;

use Ergnuor\DomainModel\DataAccess\Expression\ComparisonExpression;
use Ergnuor\DomainModel\DataAccess\Expression\CompositeExpression;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\DataAccess\Expression\Value;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionBuilder as expr;
use RuntimeException;

class FromArray
{
    private const EQ = '=';
    private const NEQ = '<>';
    private const LT = '<';
    private const LTE = '<=';
    private const GT = '>';
    private const GTE = '>=';
    private const IN = 'IN';
    private const NIN = 'NIN';
    private const CONTAINS = '*w*';
    private const NCONTAINS = 'N*w*';
    private const STARTS_WITH = 'w*';
    private const NSTARTS_WITH = 'Nw*';
    private const ENDS_WITH = '*w';
    private const NENDS_WITH = 'N*w';

    protected static array $operatorMap = [
        self::EQ => ComparisonExpression::EQ,
        self::NEQ => ComparisonExpression::NEQ,
        self::IN => ComparisonExpression::IN,
        self::NIN => ComparisonExpression::NIN,

        self::LT => ComparisonExpression::LT,
        self::LTE => ComparisonExpression::LTE,
        self::GT => ComparisonExpression::GT,
        self::GTE => ComparisonExpression::GTE,

        self::CONTAINS => ComparisonExpression::CONTAINS,
        self::NCONTAINS => ComparisonExpression::NCONTAINS,
        self::STARTS_WITH => ComparisonExpression::STARTS_WITH,
        self::NSTARTS_WITH => ComparisonExpression::NSTARTS_WITH,
        self::ENDS_WITH => ComparisonExpression::ENDS_WITH,
        self::NENDS_WITH => ComparisonExpression::NENDS_WITH,
    ];

    protected static array $oppositeOperatorMap = [
        self::EQ => self::NEQ,
        self::NEQ => self::EQ,

        self::IN => self::NIN,
        self::NIN => self::IN,

        self::GT => self::LTE,
        self::GTE => self::LT,

        self::LT => self::GTE,
        self::LTE => self::GT,

        self::CONTAINS => self::NCONTAINS,
        self::NCONTAINS => self::CONTAINS,

        self::STARTS_WITH => self::NSTARTS_WITH,
        self::NSTARTS_WITH => self::STARTS_WITH,

        self::ENDS_WITH => self::NENDS_WITH,
        self::NENDS_WITH => self::ENDS_WITH,
    ];

    public function create(?array $arrayExpression = null): ?ExpressionInterface
    {
        return $this->createExpression($arrayExpression, CompositeExpression::TYPE_AND);
    }

    protected function createExpression(array $arrayExpression, $type): CompositeExpression
    {
        $expressionList = [];

        foreach ($arrayExpression as $encodedFieldName => $value) {
            if (preg_match('/^@(AND|OR).*/i', $encodedFieldName, $m)) {
                $nextType = strtolower($m[1]) == 'and' ? CompositeExpression::TYPE_AND : CompositeExpression::TYPE_OR;

                $expressionList[] = $this->createExpression($value, $nextType);
            } else {
                $fieldName = FieldName::fromEncodedFieldName($encodedFieldName);

                if (!$fieldName->isHasSuffix()) {
                    if (is_array($value)) {
                        $expressionList[] = $this->createOperator($fieldName, self::IN, $value);
                    } else {
                        $expressionList[] = $this->createOperator($fieldName, self::EQ, $value);
                    }
                } else {
                    if (isset(self::$operatorMap[$fieldName->getSuffix()])) {
                        $expressionList[] = $this->createOperator($fieldName, $fieldName->getSuffix(), $value);
                    } else {
                        throw new RuntimeException('Unknown suffix');
                    }
                }
            }
        }

        if ($type == CompositeExpression::TYPE_AND) {
            return expr::andX(...$expressionList);
        }

        return expr::orX(...$expressionList);
    }

    private function createOperator(FieldName $fieldName, string $operator, $value): ComparisonExpression
    {
        if ($fieldName->isInverted()) {
            $operator = $this->getOppositeOperator($operator);
        }

        return new ComparisonExpression(
            $fieldName->getName(),
            $this->getComparisonOperator($operator),
            new Value($value)
        );
    }

    private function getComparisonOperator(string $operator): string
    {
        return self::$operatorMap[$operator];
    }

    private function getOppositeOperator(string $operator): string
    {
        return self::$oppositeOperatorMap[$operator];
    }
}
