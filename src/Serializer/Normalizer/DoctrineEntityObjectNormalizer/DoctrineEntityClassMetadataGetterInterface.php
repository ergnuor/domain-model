<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer\DoctrineEntityObjectNormalizer;

use Doctrine\ORM\Mapping\ClassMetadata;

interface DoctrineEntityClassMetadataGetterInterface
{
    public function getClassMetadata(string $className): ?ClassMetadata;
}