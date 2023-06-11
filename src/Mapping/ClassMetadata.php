<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Entity\DomainEntityInterface;
use Doctrine\Instantiator\Instantiator;

/**
 * todo make sure that after changes to this class it can still be cached
 */
class ClassMetadata implements ClassMetadataInterface
{
    private string $className;
    private string $repositoryClass;
    private string $persisterClass;
    private array $identifiers = [];
    private ?string $staticFactoryMethodName = null;
    private array $fields = [];
    private array $entityFields = [];
    private bool $isAggregateRoot = false;

    /** @var \ReflectionProperty[] */
    private array $refFields = [];
    /** @var \ReflectionMethod[] */
    private array $refFieldSetterMethods = [];
    /** @var \ReflectionMethod[] */
    private array $refFieldGetterMethods = [];

    private ?Instantiator $instantiator;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function newInstance(): object
    {
        return $this->instantiator->instantiate($this->className);
    }

    public function hasField(string $fieldName): bool
    {
        return isset($this->fields[$fieldName]);
    }

    private function getField(string $fieldName): array
    {
        if (!$this->hasField($fieldName)) {
            throw new \RuntimeException("Unknown field '{$fieldName}'");
        }

        return $this->fields[$fieldName];
    }

    public function isEntityField(string $fieldName): bool
    {
        $field = $this->getField($fieldName);
        return array_key_exists('entityClassName', $field);
    }

    public function getEntityClassName(string $entityFieldName): string
    {
        $field = $this->getField($entityFieldName);

        if (!array_key_exists('entityClassName', $field)) {
            throw new \RuntimeException("Unknown class name for entity field '{$entityFieldName}'");
        }

        return $field['entityClassName'];
    }

    public function isEntityCollection(string $entityFieldName): bool
    {
        $field = $this->getField($entityFieldName);

        if (!array_key_exists('isEntityCollection', $field)) {
            throw new \RuntimeException("Unknown 'isEntityCollection' attribute for entity field '{$entityFieldName}'");
        }

        return $field['isEntityCollection'];
    }

    public function getFieldNames(): array
    {
        return array_keys($this->fields);
    }

    public function getEntityFieldNames(): array
    {
        return array_keys($this->entityFields);
    }

    public function getFieldValue(object $object, string $fieldName)
    {
        $this->checkFieldExists($fieldName);

        if (isset($this->refFieldGetterMethods[$fieldName])) {
            return $this->refFieldGetterMethods[$fieldName]->invoke($object);
        }

        if (isset($this->refFields[$fieldName])) {
            return $this->refFields[$fieldName]->getValue($object);
        }

        throw new \RuntimeException(
            sprintf(
                "Can not get field '%s::%s' value",
                $this->className,
                $fieldName
            )
        );
    }

    public function setFieldValue(object $object, string $fieldName, $value)
    {
        $this->checkFieldExists($fieldName);

        if (isset($this->refFieldSetterMethods[$fieldName])) {
            $this->refFieldSetterMethods[$fieldName]->invoke($object, $value);
            return;
        }

        if (isset($this->refFields[$fieldName])) {
            $this->refFields[$fieldName]->setValue($object, $value);
            return;
        }

        throw new \RuntimeException(
            sprintf(
                "Can not set field '%s::%s' value",
                $this->className,
                $fieldName
            )
        );
    }

    private function checkFieldExists(string $fieldName)
    {
        if (!$this->hasField($fieldName)) {
            $className = $this->getClassName();
            throw new \RuntimeException("Field '{$fieldName}' not exists in '{$className}'");
        }
    }

    public function getFlattenedIdentifierFromRawData(array $data): string
    {
        $identifiers = $this->getIdentifiers();
        sort($identifiers);

        $identifierValues = [];

        foreach ($identifiers as $identifier) {
            if (
                !array_key_exists($identifier, $data) ||
                !is_scalar($data[$identifier])
            ) {
                throw new \RuntimeException("Identifier key '{$identifier}' not found in data for '{$this->className}' class name");
            }

            $identifierValues[] = (string)$data[$identifier];
        }

        return implode('_', $identifierValues);
    }

    public function getEntityIdentifierValues(DomainAggregateInterface|DomainEntityInterface $entity): array
    {
        $idValues = $this->doGetEntityIdentifierValues($entity);

        if (empty($idValues)) {
            $entityClass = get_class($entity);
            throw new \RuntimeException("Can not get {$entityClass}' identifier values");
        }

        return $idValues;
    }

    private function doGetEntityIdentifierValues(DomainAggregateInterface|DomainEntityInterface $entity): array
    {
        $idValues = [];
        foreach ($this->identifiers as $identifier) {
            $value = $this->getFieldValue($entity, $identifier);

            if ($value === null) {
                continue;
            }

            $idValues[$identifier] = $value;
        }

        if (count($idValues) > 1) {
            throw new \RuntimeException("Composite identifiers is not supported");
        }

        return $idValues;
    }

    public function hasEntityIdentifierValues(DomainAggregateInterface|DomainEntityInterface $entity): bool
    {
        $idValues = $this->doGetEntityIdentifierValues($entity);
        return !empty($idValues);
    }

    /**
     * Temporary method until we implement composite identifiers
     *
     * @param DomainAggregateInterface|DomainEntityInterface $entity
     * @return int
     */
    public function getEntityIdentifierValue(DomainAggregateInterface|DomainEntityInterface $entity): int
    {
        $idValues = $this->getEntityIdentifierValues($entity);
        return $idValues[array_key_first($idValues)];
    }

    public function getIdentifiers(): array
    {
        if (count($this->identifiers) == 0) {
            throw new \RuntimeException("Can not get identifiers to refresh for entity '{$this->className}'");
        }

        return $this->identifiers;
    }

    public function mapField($fieldMapping): void
    {
        $this->fields[$fieldMapping['fieldName']] = $fieldMapping;

        if ($fieldMapping['isId']) {
            $this->identifiers[] = $fieldMapping['fieldName'];

            if (count($this->identifiers) > 1) {
                throw new \RuntimeException("Composite identifiers is not supported. Found in '{$this->className}'");
            }
        }

        if (isset($fieldMapping['entityClassName'])) {
            $this->entityFields[$fieldMapping['fieldName']] = true;
        }
    }

    public function getStaticFactoryMethodName(): ?string
    {
        return $this->staticFactoryMethodName;
    }

    public function setStaticFactoryMethodName(?string $staticFactoryMethodName): ClassMetadata
    {
        $this->staticFactoryMethodName = $staticFactoryMethodName;
        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function wakeupReflection()
    {
        $this->instantiator = $this->instantiator ?? new Instantiator();

        foreach ($this->fields as $fieldName => $fieldMapping) {
//            if (empty($fieldMapping['writing']['writeInfo'])) {
//                throw new \RuntimeException(
//                    sprintf(
//                        "Write info for field '%s' is not defined for '%s' class metadata. Field mapping class name = '%s'.",
//                        $fieldName,
//                        $this->className,
//                        $fieldMapping['writing']['className'],
//                    )
//                );
//            }
//
//            if (empty($fieldMapping['reading']['readInfo'])) {
//                throw new \RuntimeException(
//                    sprintf(
//                        "Read info for field '%s' is not defined for '%s' class metadata. Field mapping class name = '%s'.",
//                        $fieldName,
//                        $this->className,
//                        $fieldMapping['reading']['className'],
//                    )
//                );
//            }

            $this->refFields[$fieldName] = $this->getAccessibleProperty($fieldMapping['className'], $fieldName);

            /** @var \Symfony\Component\PropertyInfo\PropertyWriteInfo $writeInfo */
            $writeInfo = $fieldMapping['writing']['writeInfo'] ?? null;

            if (
                $writeInfo !== null &&
                $writeInfo->getType() === \Symfony\Component\PropertyInfo\PropertyWriteInfo::TYPE_METHOD
            ) {
                $this->refFieldSetterMethods[$fieldName] = $this->getAccessibleMethod(
                    $fieldMapping['writing']['className'],
                    $writeInfo->getName()
                );
            }

            /** @var \Symfony\Component\PropertyInfo\PropertyReadInfo $readInfo */
            $readInfo = $fieldMapping['reading']['readInfo'] ?? null;

            if (
                $readInfo !== null &&
                $readInfo->getType() === \Symfony\Component\PropertyInfo\PropertyReadInfo::TYPE_METHOD
            ) {
                $this->refFieldGetterMethods[$fieldName] = $this->getAccessibleMethod(
                    $fieldMapping['reading']['className'],
                    $readInfo->getName()
                );
            }
        }
    }

    private function getAccessibleProperty(string $className, string $fieldName): \ReflectionProperty
    {
        $ref = new \ReflectionProperty($className, $fieldName);
        $ref->setAccessible(true);
        return $ref;
    }

    private function getAccessibleMethod(string $className, string $methodName): \ReflectionMethod
    {
        $ref = new \ReflectionMethod($className, $methodName);
        $ref->setAccessible(true);
        return $ref;
    }

    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    public function setRepositoryClass(string $repositoryClass): ClassMetadata
    {
        $this->repositoryClass = $repositoryClass;
        return $this;
    }

    public function getPersisterClass(): string
    {
        return $this->persisterClass;
    }

    public function setPersisterClass(string $persisterClass): ClassMetadata
    {
        $this->persisterClass = $persisterClass;
        return $this;
    }

    public function isAggregateRoot(): bool
    {
        return $this->isAggregateRoot;
    }

    public function setIsAggregateRoot(bool $isAggregateRoot): ClassMetadata
    {
        $this->isAggregateRoot = $isAggregateRoot;
        return $this;
    }
}