<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager;

use Ergnuor\DomainModel\Entity\DomainAggregateInterface;
use Ergnuor\DomainModel\Repository\DomainRepositoryInterface;
use Ergnuor\DomainModel\Mapping\ClassMetadataInterface;
use Ergnuor\Mapping\ClassMetadataFactoryInterface;
use Psr\Container\ContainerInterface;

class EntityManager implements EntityManagerInterface
{
    private UnitOfWorkInterface $unitOfWork;
    private ClassMetadataFactoryInterface $classMetadataFactory;
    private ContainerInterface $repositoryLocator;

    public function __construct(
        ContainerInterface $repositoryLocator,
        UnitOfWorkInterface $unitOfWork,
        ClassMetadataFactoryInterface $classMetadataFactory
    ) {
        $this->repositoryLocator = $repositoryLocator;

        $this->unitOfWork = $unitOfWork;
        $this->unitOfWork->setEntityManager($this);

        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * @param string|DomainAggregateInterface $objectOrClassName
     * @return DomainRepositoryInterface
     */
    public function getRepository(string|DomainAggregateInterface $objectOrClassName): DomainRepositoryInterface
    {
        if ($objectOrClassName instanceof DomainAggregateInterface) {
            $className = get_class($objectOrClassName);
        } elseif (is_string($objectOrClassName)) {
            $className = $objectOrClassName;
        } else {
            $interfaceName = DomainAggregateInterface::class;
            throw new \InvalidArgumentException("Class name or '{$interfaceName}' instance expected");
        }

        $classMetadata = $this->getClassMetadata($className);
        return $this->repositoryLocator->get($classMetadata->getRepositoryClass());
    }

    public function persist($object): void
    {
        $this->unitOfWork->persist($object);
    }

    public function remove($object): void
    {
        $this->unitOfWork->remove($object);
    }

    public function flush(): void
    {
        $this->unitOfWork->commit();
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    public function getClassMetadata(string $className): ClassMetadataInterface
    {
        return $this->classMetadataFactory->getMetadataFor($className);
    }

    /** @return ClassMetadataFactoryInterface<ClassMetadataInterface> */
    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        return $this->classMetadataFactory;
    }
}