<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

use Attribute;
use Ergnuor\Mapping\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Entity implements AnnotationInterface
{
    public ?string $persisterClass = null;
    public ?string $className = null;

    public function __construct(?string $persisterClass = null, ?string $className = null)
    {
        $this->persisterClass = $persisterClass;
        $this->className = $className;
    }
}