<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
class EntityCollection
{
    public string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }
}