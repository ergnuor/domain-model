<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Transaction;

use Doctrine\ORM\EntityManagerInterface;

class DoctrineTransactionManager implements TransactionManagerInterface
{
    /** @var EntityManagerInterface[] */
    private array $entityManagers;

    private bool $isTransactionActive = false;

    public function __construct(array $entityManagers)
    {
        $this->setEntityManagers($entityManagers);
    }

    private function setEntityManagers(array $entityManagers): void
    {
        if (empty($entityManagers)) {
            throw new \RuntimeException(
                sprintf(
                    'Empty entity manager list passed to "%s"',
                    get_class($this)
                )
            );
        }

        foreach ($entityManagers as $entityManager) {
            if (!($entityManager instanceof EntityManagerInterface)) {
                throw new \RuntimeException(
                    sprintf(
                        'Expected "%s" instance in "%s"',
                        EntityManagerInterface::class,
                        get_class($this)
                    )
                );
            }

            $this->entityManagers[] = $entityManager;
        }
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