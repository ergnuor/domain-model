<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Config\Field;

use Ergnuor\DomainModel\Criteria\ValueSource\ValueSourceInterface;

class Sorting
{
    private ValueSourceInterface $valueSource;

    public function __construct(
        ValueSourceInterface $valueSource,
    ) {
        $this->valueSource = $valueSource;
    }

    public function getValueSource(): ValueSourceInterface
    {
        return $this->valueSource;
    }
}