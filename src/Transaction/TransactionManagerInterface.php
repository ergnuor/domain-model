<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Transaction;

interface TransactionManagerInterface
{
    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;

    public function isTransactionActive(): bool;
}