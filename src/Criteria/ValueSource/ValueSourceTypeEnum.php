<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ValueSource;

enum ValueSourceTypeEnum
{
    case FIELD;
    case EXPRESSION;
}
