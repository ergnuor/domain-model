<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\RepositoryImplementation\Doctrine;

use Doctrine\ORM\AbstractQuery;
use Ergnuor\DomainModel\RegistryInterface;

/**
 * @template TEntity
 * @extends  AbstractDoctrineORMQueryBuilderDomainRepository<TEntity>
 */
abstract class AbstractDoctrineORMQueryBuilderServiceDomainRepository extends AbstractDoctrineORMQueryBuilderDomainRepository
{
    public function __construct(
        string $className,
        RegistryInterface $registry,
        int $listHydrationMode = AbstractQuery::HYDRATE_ARRAY,
    ) {
        parent::__construct(
            $className,
            $registry->getDomainEntityManager(),
            $registry->getConfigBuilder(),
            $registry->getDomainEntitySerializer(),
            $registry->getExpressionMapperContainer(),
            $listHydrationMode,
        );
    }
}
