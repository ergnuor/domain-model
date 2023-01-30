<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

use Ergnuor\DomainModel\Criteria\ExpressionBuilder as expr;
use Ergnuor\DomainModel\Criteria\Expression\ExpressionInterface;
use Ergnuor\DomainModel\Criteria\ExpressionHelper\ExpressionNormalizer;
use Ergnuor\DomainModel\EntityManager\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDomainRepository implements DomainRepositoryInterface
{
    protected EntityManagerInterface $domainEntityManager;
    protected Serializer $serializer;
    private string $className;

    public function __construct(
        string $className,
        EntityManagerInterface $domainEntityManager,
        Serializer $serializer
    ) {
        $this->className = $className;
        $this->domainEntityManager = $domainEntityManager;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function findById(mixed $id): ?object
    {
        return $this->findOneBy($this->getIdCriteria($id));
    }

    final public function findOneBy(array|ExpressionInterface $expression): ?object
    {
        $result = $this->findBy($expression);

        if (count($result) > 1) {
            throw new \RuntimeException('More than one item returned. Expecting one or zero items');
        }

        if (count($result) == 0) {
            return null;
        }

        return array_shift($result);
    }

    /**
     * {@inheritDoc}
     */
    final public function findBy(
        array|ExpressionInterface|null $expression = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $rawList = $this->getRawList(
            $this->prepareExpression($expression),
            $orderBy,
            $limit,
            $offset
        );

        $rawList = $this->completeRawList($rawList);

        $unitOfWork = $this->domainEntityManager->getUnitOfWork();
        $entityList = [];
        foreach ($rawList as $rawData) {
            $entityList[] = $unitOfWork->createEntity($this->className, $rawData);
        }

        return $entityList;
    }

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

    private function prepareExpression(array|ExpressionInterface|null $expression): ?ExpressionInterface
    {
        $expression = ExpressionNormalizer::normalize($expression);
        $expression = $this->modifyExpression($expression);

        return $this->addConstantFilters($expression);
    }

    protected function modifyExpression(?ExpressionInterface $expression): ?ExpressionInterface
    {
        return $expression;
    }

    private function addConstantFilters(?ExpressionInterface $expression): ?ExpressionInterface
    {
        $constantFilters = $this->getConstantFilters();

        if ($constantFilters === null) {
            return $expression;
        }

        if ($expression === null) {
            return $constantFilters;
        }

        return expr::andX(
            $constantFilters,
            $expression
        );
    }

    protected function getConstantFilters(): ?ExpressionInterface
    {
        return null;
    }

    protected function completeRawList(array $list): array
    {
        return $list;
    }

    protected function getIdCriteria($id): array
    {
        return [
            $this->getIdFieldName() => $id
        ];
    }

    protected function getIdFieldName(): string
    {
        $classMetadata = $this->domainEntityManager->getClassMetadata($this->className);
        $identifiers = $classMetadata->getIdentifiers();

        return $identifiers[0];
    }


    public function count(array|ExpressionInterface|null $expression = null): int
    {
        return $this->doCount(
            $this->prepareExpression($expression)
        );
    }

    abstract protected function doCount(?ExpressionInterface $expression = null): int;

    public function getClassName(): string
    {
        return $this->className;
    }
}
