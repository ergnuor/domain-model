<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer\DoctrineEntityObjectNormalizer;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineEntityClassMetadataGetter implements DoctrineEntityClassMetadataGetterInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function getClassMetadata(string $className): ?ClassMetadata
    {
        if ($this->entityManager->getMetadataFactory()->hasMetadataFor($className)) {
            return $this->entityManager->getClassMetadata($className);
        }

        return null;
    }
}