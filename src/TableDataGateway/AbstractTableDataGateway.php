<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Symfony\Component\Serializer\Serializer;

abstract class AbstractTableDataGateway implements TableDataGatewayInterface
{
    use TableDataGatewayTrait;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
}