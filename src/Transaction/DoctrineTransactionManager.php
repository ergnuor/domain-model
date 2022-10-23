<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Transaction;

use Ergnuor\DomainModel\DependencyInjection\DoctrineEntityListDependencyTrait;

class DoctrineTransactionManager implements TransactionManagerInterface
{
    use DoctrineEntityListDependencyTrait;

    private bool $isTransactionActive = false;

    public function __construct(array $entityManagers)
    {
        $this->setEntityManagers($entityManagers);
    }

    public function beginTransaction(): void
    {
        foreach ($this->entityManagers as $entityManager) {
            $entityManager->getConnection()->beginTransaction();
        }

        $this->isTransactionActive = true;
    }

    public function commitTransaction(): void
    {
        if (!$this->isTransactionActive) {
            throw new \RuntimeException('Trying to commit not active global transaction');
        }

        foreach ($this->entityManagers as $entityManager) {
            $entityManager->getConnection()->commit();
        }

        $this->isTransactionActive = false;
    }

    public function rollbackTransaction(): void
    {
        if (!$this->isTransactionActive) {
            throw new \RuntimeException('Trying to rollback not active global transaction');
        }

        foreach ($this->entityManagers as $entityManager) {
            $entityManager->getConnection()->rollBack();
        }

        $this->isTransactionActive = false;
    }

    public function isTransactionActive(): bool
    {
        return $this->isTransactionActive;
    }
}