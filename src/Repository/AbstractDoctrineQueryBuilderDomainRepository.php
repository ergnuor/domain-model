<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Repository;


abstract class AbstractDoctrineQueryBuilderDomainRepository extends AbstractDomainRepository
{
    use DoctrineQueryBuilderRepositoryTrait;
}
