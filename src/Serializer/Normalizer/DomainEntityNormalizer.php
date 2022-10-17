<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Entity\DomainEntityInterface;
use Ergnuor\DomainModel\EntityManager\EntityManager;
use Ergnuor\DomainModel\EntityManager\UnitOfWork;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class DomainEntityNormalizer extends \Ergnuor\DomainModel\Serializer\Normalizer\BaseObjectNormalizer
{
    public const NORMALIZE_FOR_PERSISTER = 'normalizeForPersister';

    private EntityManager $domainEntityManager;

    public function __construct(
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {

        // не используем class metadata
        parent::__construct(
            null, $nameConverter, $propertyAccessor, $propertyTypeExtractor,
            $classDiscriminatorResolver, $objectClassResolver, $defaultContext
        );
    }

    /**
     * @required
     *
     * //todo разобраться с циклическими зависимостями. Пока передаем зависимость при помощи сеттера
     */
    public function setDomainEntityManager(EntityManager $domainEntityManager)
    {
        $this->domainEntityManager = $domainEntityManager;
    }

    protected function getAdditionalCacheKeyDataToSerialize(?string $format, array $context): array
    {
        $normalizeForPersisterContext = false;
        if (isset($context[self::NORMALIZE_FOR_PERSISTER])) {
            $normalizeForPersisterContext = $context[self::NORMALIZE_FOR_PERSISTER];
        }

        return [
            'normalizeForPersister' => $normalizeForPersisterContext,
        ];
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return (
            is_subclass_of($type, DomainAggregateInterface::class, true) ||
            is_subclass_of($type, DomainEntityInterface::class, true)
        );
    }

    public function supportsNormalization($data, string $format = null)
    {
        return (
            $data instanceof DomainAggregateInterface ||
            $data instanceof DomainEntityInterface
        );
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
        $classMetadata = $this->domainEntityManager->getClassMetadata($className);
        assert($classMetadata instanceof \Ergnuor\DomainModel\Mapping\ClassMetadata);

        return $classMetadata;
    }

    /**
     * @param $classOrObject
     * @param array $context
     * @param bool $attributesAsString
     * @return false
     *
     * Мы и так берем перечень атрибутов состоящий из полей сущности @see \Ergnuor\DomainModel\Serializer\Normalizer\DomainEntityNormalizer::getAttributes.
     * То есть нам не нужна дополнительная логика для определения перечня доступных полей.
     * Symfony штуки типа "группы сериализации" тут не работают
     */
    protected function getAllowedAttributes(
        string|object $classOrObject,
        array $context,
        bool $attributesAsString = false
    ): array|bool {
        return false;
    }

    protected function getAttributes(object $object, ?string $format, array $context): array
    {
        $classMetadata = $this->getClassMetadata(get_class($object));

        if (
            !isset($context[self::NORMALIZE_FOR_PERSISTER]) ||
            !$context[self::NORMALIZE_FOR_PERSISTER]
        ) {
            return $classMetadata->getFieldNames();
        }
        
        $fieldNames = [];
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if ($classMetadata->isEntityField($fieldName)) {
                continue;
            }

            $fieldNames[] = $fieldName;
        }

        return $fieldNames;

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
             * Базовый нормализатор игнорирует обращения к не инициализированным свойствам. А мы не хотим такое игнорировать в доменных сущностях.
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
//            dump([
//                $attribute,
//                $value,
//            ]);

            $classMetadata->setFieldValue($object, $attribute, $value);
        } else {
//            dd([
//                '~~~~~~',
//                $attribute,
//                $value,
//            ]);

            parent::setAttributeValue($object, $attribute, $value, $format, $context);
        }
    }
}
