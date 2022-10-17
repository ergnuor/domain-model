<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

final class ParameterTypes
{
    //todo почистить набор типов
    public const ARRAY = 'array';
    public const INT_ARRAY = 'int_array';
    public const STR_ARRAY = 'str_array';
    public const ASCII_STRING = 'ascii_string';
    public const BIGINT = 'bigint';
    public const BINARY = 'binary';
    public const BLOB = 'blob';
    public const BOOLEAN = 'boolean';
    public const DATE_MUTABLE = 'date';
    public const DATE_IMMUTABLE = 'date_immutable';
    public const DATEINTERVAL = 'dateinterval';
    public const DATETIME_MUTABLE = 'datetime';
    public const DATETIME_IMMUTABLE = 'datetime_immutable';
    public const DATETIMETZ_MUTABLE = 'datetimetz';
    public const DATETIMETZ_IMMUTABLE = 'datetimetz_immutable';
    public const DECIMAL = 'decimal';
    public const FLOAT = 'float';
    public const GUID = 'guid';
    public const INTEGER = 'integer';
    public const JSON = 'json';
    public const OBJECT = 'object';
    public const SIMPLE_ARRAY = 'simple_array';
    public const SMALLINT = 'smallint';
    public const STRING = 'string';
    public const TEXT = 'text';
    public const TIME_MUTABLE = 'time';
    public const TIME_IMMUTABLE = 'time_immutable';

    private function __construct()
    {
    }
}
