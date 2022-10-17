<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BaseObjectNormalizer extends \Symfony\Component\Serializer\Normalizer\ObjectNormalizer
{
    /**
     * @var callable|\Closure
     */
    private $objectClassResolver;
    private ?PropertyTypeExtractorInterface $propertyTypeExtractor;
    private $typesCache = [];

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor,
            $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->propertyTypeExtractor = $propertyTypeExtractor;

        $this->objectClassResolver = $objectClassResolver ?? function ($class) {
                return \is_object($class) ? \get_class($class) : $class;
            };
    }

//    public function denormalize($data, string $type, string $format = null, array $context = [])
//    {
//        if (!isset($context['cache_key'])) {
//            $context['cache_key'] = $this->getCacheKey($format, $context);
//        }
//
//        return parent::denormalize($data, $type, $format, $context);
//    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

//        dump([
//            $type,
//            $data,
//            \App\Support\Debug::getBacktrace(10)
//        ]);

        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        $allowedAttributes = $this->getAllowedAttributes($type, $context, true);
        $normalizedData = $this->prepareForDenormalization($data);
        $extraAttributes = [];

        $reflectionClass = new \ReflectionClass($type);
        $object = $this->instantiateObject($normalizedData, $type, $context, $reflectionClass, $allowedAttributes,
            $format);
        $resolvedClass = $this->objectClassResolver ? ($this->objectClassResolver)($object) : \get_class($object);


//        if ($type === 'App\Domain\Entity\Magelan\Client\ValueObject\AuthParams') {
//        if (is_a($type, \CrmApi\EntryPoint\Rest\Entity\Crm\User::class, true)) {
//            dump([
//                $normalizedData
//            ]);
//        }

        foreach ($normalizedData as $attribute => $value) {
//            if (is_a($type, \CrmApi\EntryPoint\Rest\Entity\Crm\User::class, true)) {
//                if ($attribute == 'lastLoginAt') {
//                    dd('alsidglasidg');
//                }
////            dump([
////                $normalizedData
////            ]);
//            }

//            if (is_int($attribute)) {
//                dd([
//                    $normalizedData,
//                    $resolvedClass,
//                    $attribute,
//                    $context,
//                    get_debug_type($this),
//                    \App\Support\Debug::getBacktrace(10),
//                ]);
//            }


            $attributeContext = $this->getAttributeDenormalizationContext($resolvedClass, $attribute, $context);

            if ($this->nameConverter) {
                $attribute = $this->nameConverter->denormalize($attribute, $resolvedClass, $format, $attributeContext);
            }

            if ((false !== $allowedAttributes && !\in_array($attribute,
                        $allowedAttributes)) || !$this->isAllowedAttribute($resolvedClass, $attribute, $format,
                    $context)) {
                if (!($context[self::ALLOW_EXTRA_ATTRIBUTES] ?? $this->defaultContext[self::ALLOW_EXTRA_ATTRIBUTES])) {
                    $extraAttributes[] = $attribute;
                }

//                if (is_a($type, \CrmApi\EntryPoint\Rest\Entity\Crm\User::class, true)) {
//                    dump([
//                        'NOT',
//                        $attribute
//                    ]);
//                }

                continue;
            }

            if ($attributeContext[self::DEEP_OBJECT_TO_POPULATE] ?? $this->defaultContext[self::DEEP_OBJECT_TO_POPULATE] ?? false) {
                try {
                    $attributeContext[self::OBJECT_TO_POPULATE] = $this->getAttributeValue($object, $attribute, $format,
                        $attributeContext);
                } catch (NoSuchPropertyException $e) {
                }
            }

            $value = $this->validateAndDenormalize($resolvedClass, $attribute, $value, $format, $attributeContext);
            try {
                $this->setAttributeValue($object, $attribute, $value, $format, $attributeContext);
            } catch (InvalidArgumentException $e) {
                throw new NotNormalizableValueException(sprintf('Failed to denormalize attribute "%s" value for class "%s": ' . $e->getMessage(),
                    $attribute, $type), $e->getCode(), $e);
            }
        }

        if (!empty($extraAttributes)) {
            throw new ExtraAttributesException($extraAttributes);
        }

//        if ($type === 'App\Domain\Entity\Magelan\Client\ValueObject\AuthParams') {
//            dump([
//                $object,
//            ]);
//        }

        return $object;
    }

    /**
     * Validates the submitted data and denormalizes it.
     *
     * @param mixed $data
     *
     * @return mixed
     *
     * @throws NotNormalizableValueException
     * @throws LogicException
     */
    private function validateAndDenormalize(
        string $currentClass,
        string $attribute,
        mixed $data,
        ?string $format,
        array $context
    ) {
        if (null === $types = $this->getTypes($currentClass, $attribute)) {
            return $data;
        }

//        if ($attribute === 'privileges') {
//            dd($types);
//        }

        $expectedTypes = [];
        foreach ($types as $type) {
            if (null === $data && $type->isNullable()) {
                return null;
            }

            $collectionValueType = $type->isCollection() ? $type->getCollectionValueTypes()[0] ?? null : null;

//            if ($attribute === 'privileges') {
//                dump([
//                    $currentClass,
//                    $attribute,
//                    $collectionValueType,
//                    $type
//                ]);
//            }

            // Fix a collection that contains the only one element
            // This is special to xml format only
            if ('xml' === $format && null !== $collectionValueType && (!\is_array($data) || !\is_int(key($data)))) {
                $data = [$data];
            }

            // In XML and CSV all basic datatypes are represented as strings, it is e.g. not possible to determine,
            // if a value is meant to be a string, float, int or a boolean value from the serialized representation.
            // That's why we have to transform the values, if one of these non-string basic datatypes is expected.
            if (\is_string($data) && (XmlEncoder::FORMAT === $format || CsvEncoder::FORMAT === $format)) {
                if ('' === $data) {
                    if (Type::BUILTIN_TYPE_ARRAY === $builtinType = $type->getBuiltinType()) {
                        return [];
                    }

                    if ($type->isNullable() && \in_array($builtinType,
                            [Type::BUILTIN_TYPE_BOOL, Type::BUILTIN_TYPE_INT, Type::BUILTIN_TYPE_FLOAT], true)) {
                        return null;
                    }
                }

                switch ($builtinType ?? $type->getBuiltinType()) {
                    case Type::BUILTIN_TYPE_BOOL:
                        // according to https://www.w3.org/TR/xmlschema-2/#boolean, valid representations are "false", "true", "0" and "1"
                        if ('false' === $data || '0' === $data) {
                            $data = false;
                        } elseif ('true' === $data || '1' === $data) {
                            $data = true;
                        } else {
                            throw new NotNormalizableValueException(sprintf('The type of the "%s" attribute for class "%s" must be bool ("%s" given).',
                                $attribute, $currentClass, $data));
                        }
                        break;
                    case Type::BUILTIN_TYPE_INT:
                        if (ctype_digit($data) || '-' === $data[0] && ctype_digit(substr($data, 1))) {
                            $data = (int)$data;
                        } else {
                            throw new NotNormalizableValueException(sprintf('The type of the "%s" attribute for class "%s" must be int ("%s" given).',
                                $attribute, $currentClass, $data));
                        }
                        break;
                    case Type::BUILTIN_TYPE_FLOAT:
                        if (is_numeric($data)) {
                            return (float)$data;
                        }

                        switch ($data) {
                            case 'NaN':
                                return \NAN;
                            case 'INF':
                                return \INF;
                            case '-INF':
                                return -\INF;
                            default:
                                throw new NotNormalizableValueException(sprintf('The type of the "%s" attribute for class "%s" must be float ("%s" given).',
                                    $attribute, $currentClass, $data));
                        }

                        break;
                }
            }

            if (null !== $collectionValueType && Type::BUILTIN_TYPE_OBJECT === $collectionValueType->getBuiltinType()) {
                $builtinType = Type::BUILTIN_TYPE_OBJECT;
                $class = $collectionValueType->getClassName() . '[]';

//                if (
//                    $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT
//                ) {
//                    $childContext = $this->createChildContext($context, $attribute, $format);
//
//                    dd(
//                        $type->getClassName(),
//                        $this->serializer->supportsDenormalization($data, $type->getClassName(), $format, $childContext)
//                    );
//
//                    if ($attribute === 'privileges') {
//                        dump([
//                            'LOOK!',
//                            $currentClass,
//                            $attribute,
//                            $builtinType,
//                            $collectionValueType,
//                            $type,
//                            $this->serializer->supportsDenormalization($data, $class, $format, $childContext),
//                            get_debug_type($this->serializer),
//                            $class,
//                            $childContext,
//                            $data,
//                        ]);
//                    }
//
//                    if ($this->serializer->supportsDenormalization($data, $class, $format, $childContext)) {
//                        return $this->serializer->denormalize($data, $class, $format, $childContext);
//                    }
//                }

                if (\count($collectionKeyType = $type->getCollectionKeyTypes()) > 0) {
                    [$context['key_type']] = $collectionKeyType;
                }
            } elseif ($type->isCollection() && \count($collectionValueType = $type->getCollectionValueTypes()) > 0 && Type::BUILTIN_TYPE_ARRAY === $collectionValueType[0]->getBuiltinType()) {
                // get inner type for any nested array
                [$innerType] = $collectionValueType;

                // note that it will break for any other builtinType
                $dimensions = '[]';
                while (\count($innerType->getCollectionValueTypes()) > 0 && Type::BUILTIN_TYPE_ARRAY === $innerType->getBuiltinType()) {
                    $dimensions .= '[]';
                    [$innerType] = $innerType->getCollectionValueTypes();
                }

                if (null !== $innerType->getClassName()) {
                    // the builtinType is the inner one and the class is the class followed by []...[]
                    $builtinType = $innerType->getBuiltinType();
                    $class = $innerType->getClassName() . $dimensions;
                } else {
                    // default fallback (keep it as array)
                    $builtinType = $type->getBuiltinType();
                    $class = $type->getClassName();
                }
            } else {
                $builtinType = $type->getBuiltinType();
                $class = $type->getClassName();
            }

            $expectedTypes[Type::BUILTIN_TYPE_OBJECT === $builtinType && $class ? $class : $builtinType] = true;

            $hasReturnValue = false;
            $returnValue = null;

            if (Type::BUILTIN_TYPE_OBJECT === $builtinType) {
                if (!$this->serializer instanceof DenormalizerInterface) {

//                    dd(get_debug_type($this->serializer));

                    throw new LogicException(sprintf('Cannot denormalize attribute "%s" for class "%s" because injected serializer is not a denormalizer.',
                        $attribute, $class));
                }

                $childContext = $this->createChildContext($context, $attribute, $format);

//                if ($attribute === 'privileges') {
//                    dump([
//                        'LOOK!',
//                        $currentClass,
//                        $attribute,
//                        $builtinType,
//                        $collectionValueType,
//                        $type,
//                        $this->serializer->supportsDenormalization($data, $class, $format, $childContext),
//                        get_debug_type($this->serializer),
//                        $class,
//                        $childContext,
//                        $data,
//                    ]);
//                }

                if ($this->serializer->supportsDenormalization($data, $class, $format, $childContext)) {
                    $returnValue = $this->serializer->denormalize($data, $class, $format, $childContext);
                }

                $hasReturnValue = true;
            }

            // JSON only has a Number type corresponding to both int and float PHP types.
            // PHP's json_encode, JavaScript's JSON.stringify, Go's json.Marshal as well as most other JSON encoders convert
            // floating-point numbers like 12.0 to 12 (the decimal part is dropped when possible).
            // PHP's json_decode automatically converts Numbers without a decimal part to integers.
            // To circumvent this behavior, integers are converted to floats when denormalizing JSON based formats and when
            // a float is expected.
            if (Type::BUILTIN_TYPE_FLOAT === $builtinType && \is_int($data) && str_contains($format,
                    JsonEncoder::FORMAT)) {
                $hasReturnValue = true;
                $returnValue = (float)$data;
            }

            if (('is_' . $builtinType)($data)) {
                $hasReturnValue = true;
                $returnValue = $data;
            }

            if (!$hasReturnValue) {
                continue;
            }

            if (
                $collectionValueType !== null &&
                $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT
            ) {
                if (!$this->serializer instanceof DenormalizerInterface) {

//                    dd(get_debug_type($this->serializer));

                    throw new LogicException(sprintf('Cannot denormalize attribute "%s" collection for class "%s" because injected serializer is not a denormalizer.', $attribute, $type->getClassName()));
                }

                $childContext = $this->createChildContext($context, $attribute, $format);
                if ($this->serializer->supportsDenormalization($returnValue, $type->getClassName(), $format, $childContext)) {
                    return $this->serializer->denormalize($returnValue, $type->getClassName(), $format, $childContext);
                }
            }

            return $returnValue;
        }

        if ($context[self::DISABLE_TYPE_ENFORCEMENT] ?? $this->defaultContext[self::DISABLE_TYPE_ENFORCEMENT] ?? false) {
            return $data;
        }

        throw new NotNormalizableValueException(sprintf('The type of the "%s" attribute for class "%s" must be one of "%s" ("%s" given).',
            $attribute, $currentClass, implode('", "', array_keys($expectedTypes)), get_debug_type($data)));
    }

    /**
     * @return Type[]|null
     */
    private function getTypes(string $currentClass, string $attribute): ?array
    {
        if (null === $this->propertyTypeExtractor) {
            return null;
        }

        $key = $currentClass . '::' . $attribute;
        if (isset($this->typesCache[$key])) {
            return false === $this->typesCache[$key] ? null : $this->typesCache[$key];
        }

//        if ($attribute === 'privileges') {
//            dd(get_debug_type($this->propertyTypeExtractor));
//        }

        if (null !== $types = $this->propertyTypeExtractor->getTypes($currentClass, $attribute)) {
            return $this->typesCache[$key] = $types;
        }

        if (null !== $this->classDiscriminatorResolver && null !== $discriminatorMapping = $this->classDiscriminatorResolver->getMappingForClass($currentClass)) {
            if ($discriminatorMapping->getTypeProperty() === $attribute) {
                return $this->typesCache[$key] = [
                    new Type(Type::BUILTIN_TYPE_STRING),
                ];
            }

            foreach ($discriminatorMapping->getTypesMapping() as $mappedClass) {
                if (null !== $types = $this->propertyTypeExtractor->getTypes($mappedClass, $attribute)) {
                    return $this->typesCache[$key] = $types;
                }
            }
        }

        $this->typesCache[$key] = false;

        return null;
    }

    /**
     * Computes the denormalization context merged with current one. Metadata always wins over global context, as more specific.
     */
    private function getAttributeDenormalizationContext(string $class, string $attribute, array $context): array
    {
        if (null === $metadata = $this->getAttributeMetadata($class, $attribute)) {
            return $context;
        }

        return array_merge($context, $metadata->getDenormalizationContextForGroups($this->getGroups($context)));
    }

    private function getAttributeMetadata($objectOrClass, string $attribute): ?AttributeMetadataInterface
    {
        if (!$this->classMetadataFactory) {
            return null;
        }

        return $this->classMetadataFactory->getMetadataFor($objectOrClass)->getAttributesMetadata()[$attribute] ?? null;
    }

    protected function denormalizeParameter(
        \ReflectionClass $class,
        \ReflectionParameter $parameter,
        string $parameterName,
        $parameterData,
        array $context,
        string $format = null
    ): mixed {
        if ($parameter->isVariadic() || null === $this->propertyTypeExtractor || null === $this->propertyTypeExtractor->getTypes($class->getName(),
                $parameterName)) {
            return parent::denormalizeParameter($class, $parameter, $parameterName, $parameterData, $context, $format);
        }

        return $this->validateAndDenormalize($class->getName(), $parameterName, $parameterData, $format, $context);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        return parent::normalize($object, $format, $context);
    }

    protected function createChildContext(array $parentContext, string $attribute, ?string $format): array
    {
        $context = parent::createChildContext($parentContext, $attribute, $format);
        $context['cache_key'] = $this->getCacheKey($format, $context);

        return $context;
    }

    /**
     * Builds the cache key for the attributes cache.
     *
     * The key must be different for every option in the context that could change which attributes should be handled.
     *
     * @return bool|string
     */
    private function getCacheKey(?string $format, array $context)
    {
        foreach ($context[self::EXCLUDE_FROM_CACHE_KEY] ?? $this->defaultContext[self::EXCLUDE_FROM_CACHE_KEY] as $key) {
            unset($context[$key]);
        }
        unset($context[self::EXCLUDE_FROM_CACHE_KEY]);
        unset($context[self::OBJECT_TO_POPULATE]);
        unset($context['cache_key']); // avoid artificially different keys

        $cacheDataToSerialize = [
            'context' => $context,
            'ignored' => $context[self::IGNORED_ATTRIBUTES] ?? $this->defaultContext[self::IGNORED_ATTRIBUTES],
        ];

        $cacheDataToSerialize = array_merge(
            $cacheDataToSerialize,
            $this->getAdditionalCacheKeyDataToSerialize($format, $context)
        );

        try {
            return md5($format . serialize($cacheDataToSerialize));
        } catch (\Exception $exception) {
            // The context cannot be serialized, skip the cache
            return false;
        }
    }

    protected function getAdditionalCacheKeyDataToSerialize(?string $format, array $context): array
    {
        return [];
    }
}
