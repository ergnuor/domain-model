<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer\DoctrineEntityObjectNormalizer;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineEntityClassMetadataGetter implements DoctrineEntityClassMetadataGetterInterface
{
    /** @var EntityManagerInterface[] */
    private $entityManagers;

    public function __construct(
        $entityManagers,
    ) {
        $this->setEntityManagers($entityManagers);
    }

    private function setEntityManagers(array $entityManagers): void
    {
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

    public function getClassMetadata(string $className): ?ClassMetadata
    {
        foreach ($this->entityManagers as $entityManager) {
            if ($entityManager->getMetadataFactory()->hasMetadataFor($className)) {
                return $entityManager->getClassMetadata($className);
            }
        }

        return null;
    }
}