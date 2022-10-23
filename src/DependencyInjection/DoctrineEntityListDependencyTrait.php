<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;

trait DoctrineEntityListDependencyTrait
{
    /** @var EntityManagerInterface[] */
    private array $entityManagers;

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
}