<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
class AggregateRoot
{
    public string $repositoryClass;

    public string $persisterClass;

    public function __construct(string $repositoryClass, string $persisterClass)
    {
        $this->repositoryClass = $repositoryClass;
        $this->persisterClass = $persisterClass;
    }
}