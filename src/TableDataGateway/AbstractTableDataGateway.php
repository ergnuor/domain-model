<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\TableDataGateway;

use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;

abstract class AbstractTableDataGateway implements TableDataGatewayInterface
{
    use TableDataGatewayTrait;

    public function __construct(JsonSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}