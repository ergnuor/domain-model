<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

use Attribute;
use Ergnuor\Mapping\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Internal implements AnnotationInterface
{

}