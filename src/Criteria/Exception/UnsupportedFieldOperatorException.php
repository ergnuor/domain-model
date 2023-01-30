<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Exception;

use Ergnuor\DomainModel\Criteria\Expression\Expression;

class UnsupportedFieldOperatorException extends \RuntimeException
{
    public static function fromExpression(Expression $expression): UnsupportedFieldOperatorException
    {
        return new UnsupportedFieldOperatorException(
            sprintf(
                "Unsupported operator '%s' for field '%s'",
                $expression->getOperator(),
                $expression->getFieldName(),
            )
        );
    }
}