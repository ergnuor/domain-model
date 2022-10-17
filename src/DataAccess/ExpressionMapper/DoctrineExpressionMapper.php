<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

class DoctrineExpressionMapper extends ExpressionMapper
{
    public function __construct(?array $fieldMap = null)
    {
        parent::__construct(
            new DoctrineParameterMapper(),
            new DoctrineBasicExpressionMapper(),
            $fieldMap,
        );
    }
}