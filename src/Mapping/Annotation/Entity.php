<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
class Entity
{
    public ?string $persisterClass = null;
    public ?string $className = null;

    public function __construct(?string $persisterClass = null, ?string $className = null)
    {
        $this->persisterClass = $persisterClass;
        $this->className = $className;
    }
}