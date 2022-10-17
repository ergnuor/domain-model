<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping;

use Ergnuor\DomainModel\Mapping\ClassMetadataFactoryAdapterInterface;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class ClassMetadataFactoryAdapter implements ClassMetadataFactoryAdapterInterface
{
    private Reader $reader;
    private string $entityDir;

    public function __construct(
        Reader $reader,
        string $entityDir
    ) {
        $this->reader = new IndexedReader($reader);
        $this->entityDir = $entityDir;
    }

    public function getClassNames(): array
    {
        $classes = [];
        $includedFiles = [];

        if (!is_dir($this->entityDir)) {
            throw new \RuntimeException("Directory not exists '{$this->entityDir}");
        }

//        $iterator = new \RegexIterator(
//            new \DirectoryIterator($this->entityDir),
//            '/^.+.php$/i',
//            \RecursiveRegexIterator::GET_MATCH
//        );

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->entityDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^.+.php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

//        $iterator =  new \RecursiveIteratorIterator(
//                new \RecursiveDirectoryIterator($this->entityDir, \FilesystemIterator::SKIP_DOTS),
//                \RecursiveIteratorIterator::LEAVES_ONLY
//            );

//        $iterator = new \DirectoryIterator($this->entityDir);

        foreach ($iterator as $file) {
//            $sourceFile = $this->entityDir . '/' . $file[0];
            $sourceFile = $file[0];

            if (!preg_match('(^phar:)i', $sourceFile)) {
                $sourceFile = realpath($sourceFile);
            }

//            dd($sourceFile);

            require_once $sourceFile;

            $includedFiles[] = $sourceFile;
        }

//        dd($includedFiles);

        $declared = get_declared_classes();

//        dd($declared);

        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (
                $rc->isInterface() ||
                !in_array($sourceFile, $includedFiles) ||
                $this->isTransient($className)
            ) {
                continue;
            }

            $classes[] = $className;
        }

        return $classes;
    }

    private function isTransient(string $className): bool
    {
        $classAnnotations = $this->reader->getClassAnnotations(new \ReflectionClass($className));

        return !isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class]) && !isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class]);
    }

    public function isCorrectCachedInstance($cachedMetadata): bool
    {
        return $cachedMetadata instanceof ClassMetadata;
    }

    /**
     * @param ClassMetadata $cachedMetadata
     * @return void
     */
    public function afterGotFromCache($cachedMetadata): void
    {
        $cachedMetadata->wakeupReflection();
    }

    public function loadMetadata(string $className)
    {
        $reversedClassHierarchy = $this->getReversedClassHierarchyInfo($className);
        $classHierarchyReflectionClass = $this->getClassHierarchyReflectionClass($className);
        $reversedClassHierarchy[$className] = $classHierarchyReflectionClass;
        $classNameReflectionClass = $classHierarchyReflectionClass;

        $classAnnotations = $this->reader->getClassAnnotations($classNameReflectionClass);

        if (
            (isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class]) && isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class])) ||
            (!isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class]) && !isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class]))
        ) {
            $annotationAggregateRootClass = \Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class;
            $annotationEntityClass = \Ergnuor\DomainModel\Mapping\Annotation\Entity::class;
            throw new \RuntimeException("Entity '$className' should be marked either as '{$annotationAggregateRootClass}' or '{$annotationEntityClass}'");
        }

        $classMetadata = new ClassMetadata($className);

        if (isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class])) {
            $aggregateRootAnnotation = $classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot::class];
            assert($aggregateRootAnnotation instanceof \Ergnuor\DomainModel\Mapping\Annotation\AggregateRoot);

            $classMetadata->setRepositoryClass($aggregateRootAnnotation->repositoryClass);
            $classMetadata->setPersisterClass($aggregateRootAnnotation->persisterClass);
            $classMetadata->setIsAggregateRoot(true);
        }

        if (isset($classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class])) {
            $entityAnnotation = $classAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class];
            assert($entityAnnotation instanceof \Ergnuor\DomainModel\Mapping\Annotation\Entity);

            $classMetadata->setPersisterClass($entityAnnotation->persisterClass);
            $classMetadata->setIsAggregateRoot(false);
        }

        $reflectionExtractor = new ReflectionExtractor(
            null,
            null,
            null,
            false,
            ReflectionExtractor::ALLOW_PUBLIC | ReflectionExtractor::ALLOW_PROTECTED | ReflectionExtractor::ALLOW_PRIVATE,
            null,
            ReflectionExtractor::DISALLOW_MAGIC_METHODS
        );

        $fieldMappings = [];
        $properties = [];
        $propertyNamesByClassName = [];

        // Свойства собираем в порядке от родителя к потомку
        foreach ($reversedClassHierarchy as $currentClassName => $currentClassNameReflectionClass) {
            foreach ($currentClassNameReflectionClass->getProperties() as $property) {

                $propertyAnnotations = $this->reader->getPropertyAnnotations($property);

                if (isset($propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Internal::class])) {
                    continue;
                }

                $this->throwIfClassPropertyIsInvalid($property, $className, $properties);

                $properties[$property->getName()] = $property;

                $propertyNamesByClassName[$currentClassName] = $propertyNamesByClassName[$currentClassName] ?? [];
                $propertyNamesByClassName[$currentClassName][$property->getName()] = $property->getName();


                $fieldMapping = [
                    'isId' => false,
                    'fieldName' => $property->getName(),
                    'className' => $currentClassName,
                    'reading' => [
                        'className' => $currentClassName,
                        'readInfo' => null,
                    ],
                    'writing' => [
                        'className' => $currentClassName,
                        'writeInfo' => null,
                    ],
                ];

                if (isset($propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class])) {
                    $entityAnnotation = $propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Entity::class];
                    assert($entityAnnotation instanceof \Ergnuor\DomainModel\Mapping\Annotation\Entity);

                    $fieldMapping['entityClassName'] = $entityAnnotation->className;
                    $fieldMapping['isEntityCollection'] = false;
                }

                if (isset($propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\EntityCollection::class])) {
                    $entityCollectionAnnotation = $propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\EntityCollection::class];
                    assert($entityCollectionAnnotation instanceof \Ergnuor\DomainModel\Mapping\Annotation\EntityCollection);

                    $fieldMapping['entityClassName'] = $entityCollectionAnnotation->className;
                    $fieldMapping['isEntityCollection'] = true;
                }

                $fieldMapping = $this->setFieldAccessInfo(
                    $fieldMapping,
                    $property,
                    $className,
                    $classNameReflectionClass,
                    $currentClassName,
                    $currentClassNameReflectionClass,
                    $reflectionExtractor,
                );

                $fieldMappings[$property->getName()] = $fieldMapping;
            }
        }

        // ID поле ищем в порядке от потомка к родителю
        $isIdFound = false;
        foreach (array_reverse($reversedClassHierarchy) as $currentClassName => $currentClassNameReflectionClass) {
            foreach ($propertyNamesByClassName[$currentClassName] as $propertyName) {
                $property = $properties[$propertyName];
                $propertyAnnotations = $this->reader->getPropertyAnnotations($property);

                if (isset($propertyAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\Id::class])) {
                    $fieldMappings[$propertyName]['isId'] = true;
                    $isIdFound = true;
                    break;
                }
            }

            if ($isIdFound) {
                break;
            }
        }

        foreach ($fieldMappings as $fieldMapping) {
            $classMetadata->mapField($fieldMapping);
        }

        //todo искать ли фабричные методы в родительских классах?
        foreach ($classNameReflectionClass->getMethods() as $method) {
            $methodAnnotations = $this->reader->getMethodAnnotations($method);

            if (isset($methodAnnotations[\Ergnuor\DomainModel\Mapping\Annotation\FactoryMethod::class])) {
                if (!$method->isStatic()) {
                    $annotationText = \Ergnuor\DomainModel\Mapping\Annotation\FactoryMethod::class;
                    throw new \RuntimeException("Only static methods can be marked as '{$annotationText}' in '$className'");
                }

                if ($classMetadata->getStaticFactoryMethodName() !== null) {
                    $annotationText = \Ergnuor\DomainModel\Mapping\Annotation\FactoryMethod::class;
                    throw new \RuntimeException("Only one method can be marked as '{$annotationText}' in '$className'");
                }

                $classMetadata->setStaticFactoryMethodName($method->getName());
            }
        }

        return $classMetadata;
    }

    /**
     * @param string $className
     * @return array<string, \ReflectionClass>
     */
    private function getReversedClassHierarchyInfo(string $className): array
    {
        $classHierarchy = [];

        $parentClassNames = class_parents($className);

        if ($parentClassNames === false) {
            throw new \RuntimeException("Can not get class parents for {$className}");
        }

        foreach ($parentClassNames as $nextClassName) {
            $classHierarchy[$nextClassName] = $this->getClassHierarchyReflectionClass($nextClassName);
        }

        return array_reverse($classHierarchy);
    }

    private function getClassHierarchyReflectionClass(string $className): \ReflectionClass
    {
        return new \ReflectionClass($className);
    }

    private function throwIfClassPropertyIsInvalid(
        \ReflectionProperty $property,
        string $className,
        array $properties
    ): void {
        if (
            $property->isPrivate() &&
            $property->class !== $className
        ) {
            throw new \RuntimeException(
                sprintf(
                    "Error while loading metadata for class '%s': can not have private  properties (entity fields) in parent entities. Found in '%s'",
                    $className,
                    $property->class . ':' . $property->getName(),
                )
            );
        }

        if (
            isset($properties[$property->getName()]) &&
            $properties[$property->getName()]->class !== $property->class
        ) {
            throw new \RuntimeException(
                sprintf(
                    "Error while loading metadata for class '%s': can not redeclare property '%s' in child class '%s'. Already declared in parent class '%s'",
                    $className,
                    $property->getName(),
                    $property->class,
                    $properties[$property->getName()]->class,
                )
            );
        }
    }

    private function setFieldAccessInfo(
        array $fieldMapping,
        \ReflectionProperty $property,
        string $className,
        \ReflectionClass $classNameReflectionClass,
        string $currentClassName,
        mixed $currentClassNameReflectionClass,
        ReflectionExtractor $reflectionExtractor,
    ): array {
        //Смотрим сначала в рамках основного класса,
        //т.к. методы расположенные в родительских классах (даже приватные) доступны через рефлексию и относительно основного класса
        $readInfo = $reflectionExtractor->getReadInfo($className, $property->getName());

        if ($readInfo !== null) {
            $fieldMapping = $this->setReadingInfo($readInfo, $classNameReflectionClass, $fieldMapping);
        } else {

            //Смотрим в рамках текущего (в итерации по дереву) класса
            $readInfo = $reflectionExtractor->getReadInfo($currentClassName, $property->getName());

            if ($readInfo !== null) {
                $fieldMapping = $this->setReadingInfo($readInfo, $currentClassNameReflectionClass, $fieldMapping);
            }
        }

        $writeInfo = $reflectionExtractor->getWriteInfo($className, $property->getName());

        if ($writeInfo !== null) {
            $fieldMapping = $this->setWritingInfo($writeInfo, $classNameReflectionClass, $fieldMapping);
        } else {

            $writeInfo = $reflectionExtractor->getWriteInfo($currentClassName, $property->getName());

            if ($writeInfo !== null) {
                $fieldMapping = $this->setWritingInfo($writeInfo, $currentClassNameReflectionClass, $fieldMapping);
            }
        }

        return $fieldMapping;
    }

    private function setReadingInfo(
        \Symfony\Component\PropertyInfo\PropertyReadInfo $readInfo,
        \ReflectionClass $classNameReflectionClass,
        array $fieldMapping
    ): array {
        if ($readInfo->getType() === \Symfony\Component\PropertyInfo\PropertyReadInfo::TYPE_METHOD) {
            $readMethod = $classNameReflectionClass->getMethod($readInfo->getName());

            $fieldMapping['reading'] = [
                'readInfo' => $readInfo,
                'className' => $readMethod->class,
            ];
        } elseif ($readInfo->getType() === \Symfony\Component\PropertyInfo\PropertyReadInfo::TYPE_PROPERTY) {
            $readProperty = $classNameReflectionClass->getProperty($readInfo->getName());

            $fieldMapping['reading'] = [
                'readInfo' => $readInfo,
                'className' => $readProperty->class,
            ];
        }
        return $fieldMapping;
    }

    private function setWritingInfo(
        \Symfony\Component\PropertyInfo\PropertyWriteInfo $writeInfo,
        \ReflectionClass $classNameReflectionClass,
        array $fieldMapping
    ): array {
        if ($writeInfo->getType() === \Symfony\Component\PropertyInfo\PropertyWriteInfo::TYPE_METHOD) {
            $readMethod = $classNameReflectionClass->getMethod($writeInfo->getName());

            $fieldMapping['writing'] = [
                'writeInfo' => $writeInfo,
                'className' => $readMethod->class,
            ];
        } elseif ($writeInfo->getType() === \Symfony\Component\PropertyInfo\PropertyWriteInfo::TYPE_PROPERTY) {
            $readProperty = $classNameReflectionClass->getProperty($writeInfo->getName());

            $fieldMapping['writing'] = [
                'writeInfo' => $writeInfo,
                'className' => $readProperty->class,
            ];
        }
        return $fieldMapping;
    }

    /**
     * @param ClassMetadata $cachedMetadata
     * @return void
     */
    public function afterMetadataLoaded($cachedMetadata): void
    {
        $cachedMetadata->wakeupReflection();
    }
}