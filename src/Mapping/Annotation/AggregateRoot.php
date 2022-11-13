<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

use Attribute;
use Ergnuor\Mapping\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AggregateRoot implements AnnotationInterface
{
    public string $repositoryClass;

    public string $persisterClass;

    public function __construct(string $repositoryClass, string $persisterClass)
    {
        $this->repositoryClass = $repositoryClass;
        $this->persisterClass = $persisterClass;
    }
}