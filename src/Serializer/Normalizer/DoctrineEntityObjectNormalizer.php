<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Ergnuor\DomainModel\Serializer\Normalizer\DoctrineEntityObjectNormalizer\DoctrineEntityClassMetadataGetterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class DoctrineEntityObjectNormalizer extends \Ergnuor\DomainModel\Serializer\Normalizer\BaseObjectNormalizer
{
    public const SKIP_NOT_INITIALIZED_PROXIES = 'skipNotInitializedProxies';
    private DoctrineEntityClassMetadataGetterInterface $classMetadataGetter;

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = null,
        DoctrineEntityClassMetadataGetterInterface $classMetadataGetter
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor,
            $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->defaultContext = array_merge(
            $this->defaultContext,
            [
                self::SKIP_NOT_INITIALIZED_PROXIES => true,
//                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (
//                    $object,
//                    $format,
//                    $context
//                ) {
//                    return null;
//                }
            ]
        );

        $this->classMetadataGetter = $classMetadataGetter;
    }

    protected function getAdditionalCacheKeyDataToSerialize(?string $format, array $context): array
    {
        return [
            'skipNotInitializedProxies' => $this->isSkipNotInitializedProxies(),
        ];
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return false;
    }

    private function isSkipNotInitializedProxies(): bool
    {
        return (bool)($context[self::SKIP_NOT_INITIALIZED_PROXIES] ?? $this->defaultContext[self::SKIP_NOT_INITIALIZED_PROXIES] ?? true);
    }

    protected function getAttributes(object $object, ?string $format, array $context): array
    {
        $attributes = parent::getAttributes($object, $format, $context);

        $attributes = array_flip($attributes);

        $className = get_class($object);

        $classMetadata = $this->getClassMetadata($className);

        $associationNames = array_flip($classMetadata->getAssociationNames());

        $reflectionClass = new \ReflectionClass($className);

        /** @var \ReflectionProperty[] $associationProperties */
        $associationProperties = [];
        foreach ($reflectionClass->getProperties() as $property) {
            if (
                isset($associationNames[$property->getName()]) &&
                isset($attributes[$property->getName()])
            ) {
                $associationProperties[] = $property;
            }
        }

//            if ($object instanceof \App\Infrastructure\Doctrine\Entity\Crm\AclRoleHierarchy) {
//                dump($associationProperties);
//            }

        foreach ($associationProperties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

//            if ($object instanceof \App\Infrastructure\Doctrine\Entity\Crm\AclRoleHierarchy) {
//                dump([
//                    $property->getName(),
//                    $value,
//                    spl_object_hash($value),
//                    $context
//                ]);
//            }

            if ($value instanceof \Doctrine\Common\Proxy\Proxy) {
                if (
                    $this->isSkipNotInitializedProxies() &&
                    !$value->__isInitialized()
                ) {
                    unset($attributes[$property->getName()]);
                }
            } elseif (is_object($value)) {
                $objectHash = spl_object_hash($value);
                if (
                    isset($context[AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT_COUNTERS]) &&
                    isset($context[AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT_COUNTERS][$objectHash])
                ) {
                    unset($attributes[$property->getName()]);
                }
            }
        }

        $attributes = array_flip($attributes);

//        if ($object instanceof \App\Infrastructure\Doctrine\Entity\Crm\AclRoleHierarchy) {
//            dd([
//                $attributes,
//                $this->isSkipNotInitializedProxies(),
//            ]);
//        }

        return $attributes;
    }

    public function supportsNormalization($data, string $format = null)
    {
//        dump(get_debug_type($data));

        if (!is_object($data)) {
            return false;
        }

        $classMetadata = $this->getClassMetadata($data);

//        if ($data instanceof \App\Infrastructure\Doctrine\Entity\Crm\AclRole) {
////            dd($classMetadata);
//        }

        if ($classMetadata === null) {
            return false;
        }

        return true;

//        dd([
//            $classMetadata->getName(),
//            $classMetadata->getAssociationNames(),
//        ]);
//
////        $classMetadata->getAssociationNames()
//
//        dump($classMetadata->getAssociationNames());

//        dd([
//            $data,
//            $this->classMetadataGetter->getClassMetadata($className)
//        ]);
    }

    private function getClassMetadata($objectOrClass): ?\Doctrine\ORM\Mapping\ClassMetadata
    {
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        } elseif (is_string($objectOrClass)) {
            $className = $objectOrClass;
        } else {
            $type = get_debug_type($objectOrClass);
            throw new \RuntimeException("Object or class name expected. '{$type}' given.'");
        }

        return $this->classMetadataGetter->getClassMetadata($className);
    }
}
