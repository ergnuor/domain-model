<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer\DoctrineEntityObjectNormalizer;

use Doctrine\ORM\Mapping\ClassMetadata;
use Ergnuor\DomainModel\DependencyInjection\DoctrineEntityListDependencyTrait;

class DoctrineEntityClassMetadataGetter implements DoctrineEntityClassMetadataGetterInterface
{
    use DoctrineEntityListDependencyTrait;

    public function __construct(array $entityManagers)
    {
        $this->setEntityManagers($entityManagers);
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