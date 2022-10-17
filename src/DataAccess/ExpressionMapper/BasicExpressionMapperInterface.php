<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\ExpressionMapper;

interface BasicExpressionMapperInterface
{
    public function orX(...$x);
    public function andX(...$x);

    public function eq(string $field, string $placeholder);
    public function neq(string $field, string $placeholder);

    public function like(string $field, string $placeholder);
    public function notLike(string $field, string $placeholder);

    public function isMemberOf(string $field, string $placeholder);

    public function comparison(string $field, string $operator, string $placeholder);

    public function in(string $field, string $placeholder);
    public function notIn(string $field, string $placeholder);

    public function isNull(string $field);
    public function isNotNull(string $field);

    public function not($expression);
}