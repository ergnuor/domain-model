<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Mapping\Annotation;

use Attribute;
use Ergnuor\Mapping\Annotation\AnnotationInterface;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class FactoryMethod implements AnnotationInterface
{

}