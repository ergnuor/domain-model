<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

abstract class AbstractDoctrineQueryBuilderServiceTableDataGateway extends AbstractServiceTableDataGateway
{
    use DoctrineQueryBuilderTableDataGatewayTrait;
}