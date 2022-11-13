<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

use Attribute;
use Ergnuor\Mapping\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityCollection implements AnnotationInterface
{
    public string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }
}