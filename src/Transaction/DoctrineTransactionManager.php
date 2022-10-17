<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Transaction;

use Doctrine\ORM\EntityManagerInterface;

class DoctrineTransactionManager implements TransactionManagerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function beginTransaction(): void
    {
        $this->entityManager->getConnection()->beginTransaction();
    }

    public function commitTransaction(): void
    {
        if (!$this->isTransactionActive()) {
            throw new \RuntimeException('Trying to commit not active global transaction');
        }

        $this->entityManager->getConnection()->commit();
    }

    public function rollbackTransaction(): void
    {
        if (!$this->isTransactionActive()) {
            throw new \RuntimeException('Trying to rollback not active global transaction');
        }

        $this->entityManager->getConnection()->rollBack();
    }

    public function isTransactionActive(): bool
    {
        return $this->entityManager->getConnection()->isTransactionActive();
    }
}