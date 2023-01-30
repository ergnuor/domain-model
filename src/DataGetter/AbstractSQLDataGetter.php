<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DataGetter;

use Doctrine\DBAL\Connection;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractSQLDataGetter extends AbstractDataGetter
{
    public const QUERY_WHERE_PLACEHOLDER = '::where::';
    public const QUERY_ORDER_BY_PLACEHOLDER = '::orderBy::';

    protected Connection $connection;

    public function __construct(
        Connection $connection,
        ExpressionMapperInterface $expressionMapper,
        OrderMapperInterface $orderMapper,
        ?Serializer $serializer = null,
    ) {
        parent::__construct($expressionMapper, $orderMapper, $serializer);
        $this->connection = $connection;
    }

    protected function modifyLimitQuery($query, $limit, $offset = 0): string
    {
        return $this->connection->getDriver()->getDatabasePlatform()->modifyLimitQuery($query, $limit, $offset);
    }

    protected function injectWhereIntoSql(string $sql, string $where): string
    {
        return $this->injectStringIntoQueryIntoSql(
            $sql,
            $where,
            self::QUERY_WHERE_PLACEHOLDER
        );
    }

    protected function injectOrderByIntoSql(string $sql, string $orderBy): string
    {
        return $this->injectStringIntoQueryIntoSql(
            $sql,
            $orderBy,
            self::QUERY_ORDER_BY_PLACEHOLDER
        );
    }

    private function injectStringIntoQueryIntoSql(string $query, string $replacement, string $placeholder): string
    {
        preg_match_all('/(?P<placeholder>' . preg_quote($placeholder) . ')/', $query, $m);

        if (count($m['placeholder']) > 1) {
            throw new \RuntimeException(
                sprintf(
                    "More than one '%s' placeholder found in query '%s'",
                    $placeholder,
                    $query
                )
            );
        }

        if (count($m['placeholder']) < 1) {
            throw new \RuntimeException(
                sprintf(
                    "'%s' placeholder not found in query '%s'",
                    $placeholder,
                    $query
                )
            );
        }

        return preg_replace(
            '/' . preg_quote($placeholder) . '/',
            $replacement,
            $query
        );
    }
}