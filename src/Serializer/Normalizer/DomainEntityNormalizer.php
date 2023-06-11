<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Ergnuor\DomainModel\Entity\DomainEntityInterface;
use Ergnuor\DomainModel\EntityManager\UnitOfWork;
use Ergnuor\Mapping\ClassMetadataFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DomainEntityNormalizer extends \Ergnuor\Serializer\Normalizer\ObjectNormalizer
{
    public const NORMALIZE_FOR_PERSISTER = __CLASS__ . 'normalizeForPersister';

    private ClassMetadataFactoryInterface $domainEntityClassMetadataFactory;

    private array $attributesCache = [];

    public function __construct(
        ClassMetadataFactoryInterface $domainEntityClassMetadataFactory,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        // we do not use class metadata
        parent::__construct(
            null, $nameConverter, $propertyAccessor, $propertyTypeExtractor,
            $classDiscriminatorResolver, $objectClassResolver, $defaultContext
        );

        $this->defaultContext = array_merge(
            $this->defaultContext,
            [
                self::NORMALIZE_FOR_PERSISTER => false,
            ]
        );

        $this->domainEntityClassMetadataFactory = $domainEntityClassMetadataFactory;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_subclass_of($type, DomainEntityInterface::class);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof DomainEntityInterface;
    }

    protected function instantiateObject(
        array &$data,
        string $class,
        array &$context,
        \ReflectionClass $reflectionClass,
        $allowedAttributes,
        string $format = null
    ) {
        if (isset($context[UnitOfWork::CONTEXT_OBJECT_TO_REFRESH])) {
            return $context[UnitOfWork::CONTEXT_OBJECT_TO_REFRESH];
        }

        $classMetadata = $this->getClassMetadata($class);

        return $classMetadata->newInstance();
    }

    private function getClassMetadata(string $className): \Ergnuor\DomainModel\Mapping\ClassMetadata
    {
        $classMetadata = $this->domainEntityClassMetadataFactory->getMetadataFor($className);
        assert($classMetadata instanceof \Ergnuor\DomainModel\Mapping\ClassMetadata);

        return $classMetadata;
    }

    /**
     * @param string|object $classOrObject
     * @param array $context
     * @param bool $attributesAsString
     * @return array|bool
     *
     * We already take the list of attributes from entity (@see \Ergnuor\DomainModel\Serializer\Normalizer\DomainEntityNormalizer::getAttributes),
     * that is, we do not need additional logic to determine the list of available attributes.
     * Symfony serialization groups don't work here
     */
    protected function getAllowedAttributes(
        string|object $classOrObject,
        array $context,
        bool $attributesAsString = false
    ): array|bool {
        return false;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /**
         * Base normalizer can ignore calls to uninitialized properties. We do not want to ignore them in domain entities.
         * {@see \Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::isUninitializedValueError}
         */
        $context[self::SKIP_UNINITIALIZED_VALUES] = false;

        return parent::normalize($object, $format, $this->normalizeContext($context));
    }

    /**
     * We normalize the context for ease of use and for uniformity,
     * because the context is used when generating the caching key in the @see ObjectNormalizer::getCacheKey method
     */
    private function normalizeContext(array $context): array
    {
        $context = array_replace(
            $this->defaultContext,
            $context
        );

        $context[self::NORMALIZE_FOR_PERSISTER] = (bool)$context[self::NORMALIZE_FOR_PERSISTER];

        return $context;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        return parent::denormalize($data, $type, $format, $this->normalizeContext($context));
    }

    protected function getAttributes(object $object, ?string $format, array $context): array
    {
        $isNormalizeForPersister = $context[self::NORMALIZE_FOR_PERSISTER];

        $class = $this->objectClassResolver ? ($this->objectClassResolver)($object) : \get_class($object);
        $key = $class . '-' . $isNormalizeForPersister ? 'for-persister' : 'not-for-persister' . $context['cache_key'];


        if (isset($this->attributesCache[$key])) {
            return $this->attributesCache[$key];
        }

        $classMetadata = $this->getClassMetadata(get_class($object));

        if (!$isNormalizeForPersister) {
            $attributes = $classMetadata->getFieldNames();
        } else {

            $attributes = [];
            foreach ($classMetadata->getFieldNames() as $fieldName) {
                if ($classMetadata->isEntityField($fieldName)) {
                    continue;
                }

                $attributes[] = $fieldName;
            }
        }

        $this->attributesCache[$key] = $attributes;


        return $attributes;

//        dd(
//            $fieldNames,
//            $context,
//            parent::getAttributes($object, $format, $context)
//        );
//
//        $attributes = array_merge(
//            parent::getAttributes($object, $format, $context),
//            $fieldNames,
//        );
//
//        dump($attributes);
//
//        return array_unique($attributes);
    }

    protected function getAttributeValue(
        object $object,
        string $attribute,
        string $format = null,
        array $context = []
    ): mixed {
        $classMetadata = $this->getClassMetadata(get_class($object));

        try {
            if ($classMetadata->hasField($attribute)) {
                return $classMetadata->getFieldValue($object, $attribute);
            }
        } catch (\Error $e) {
            /**
             * Base normalizer can ignore calls to uninitialized properties. We do not want to ignore them in domain entities.
             * {@see \Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::isUninitializedValueError}
             */
            throw new DomainEntityNormalizerException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return parent::getAttributeValue($object, $attribute, $format, $context);
    }

    protected function setAttributeValue(
        object $object,
        string $attribute,
        $value,
        string $format = null,
        array $context = []
    ) {
        $classMetadata = $this->getClassMetadata(get_class($object));

        if ($classMetadata->hasField($attribute)) {
            $classMetadata->setFieldValue($object, $attribute, $value);
        } else {
            parent::setAttributeValue($object, $attribute, $value, $format, $context);
        }
    }
}
