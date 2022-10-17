<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;

abstract class AbstractDoctrineQueryBuilderServiceDomainRepository extends AbstractServiceDomainRepository
{
    use DoctrineQueryBuilderRepositoryTrait;
}
