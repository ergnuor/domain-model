<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;

trait DomainRepositoryTrait
{
    use RepositoryTrait;

    protected EntityManagerInterface $domainEntityManager;
    protected JsonSerializerInterface $serializer;

    final protected function doFindBy(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $rawList = $this->getRawList(
            $expression,
            $orderBy,
            $limit,
            $offset
        );

        $unitOfWork = $this->domainEntityManager->getUnitOfWork();
        $entityList = [];
        foreach ($rawList as $rawData) {
            $entityList[] = $unitOfWork->createEntity($this->getClassName(), $rawData);
        }

        return $entityList;
    }

    /**
     * @param $id
     * @param $object
     * @return void
     *
     * @deprecated Временное решение для гидрации новых объектов
     */
//    final public function refresh(array $idCriteria, $object)
//    {
//        $rawList = $this->doFindRawBy(
//            $this->normalizeCriteria($idCriteria)
//        );
//
//        $unitOfWork = $this->domainEntityManager->getUnitOfWork();
//
//        $context = [
//            UnitOfWorkInterface::CONTEXT_OBJECT_TO_REFRESH => $object,
//        ];
//        $unitOfWork->createEntity($this->getClassName(), $rawList[0], $context);
//    }

    /**
     * @param ExpressionInterface|null $expression
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    abstract protected function getRawList(
        ?ExpressionInterface $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    abstract public function getClassName(): string;

    protected function getIdFieldName(): string
    {
        $classMetadata = $this->domainEntityManager->getClassMetadata($this->getClassName());
        $identifiers = $classMetadata->getIdentifiers();

        return $identifiers[0];
    }
}
