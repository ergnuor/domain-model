<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Ergnuor\DomainModel\RegistryInterface;

abstract class AbstractServiceTableDataGateway extends AbstractTableDataGateway
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry->getTableDataGatewayDTOSerializer());
    }
}