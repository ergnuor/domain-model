<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

abstract class AbstractDoctrineQueryBuilderTableDataGateway extends AbstractTableDataGateway
{
    use DoctrineQueryBuilderTableDataGatewayTrait;
}