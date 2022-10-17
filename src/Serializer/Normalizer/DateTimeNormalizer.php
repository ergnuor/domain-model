<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class DateTimeNormalizer extends \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer
{
    public const NORMALIZE_AS_OBJECT = 'normalizer.datetime.context.as_object';

    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

//        if (
//            isset($context[DateTimeNormalizer::NORMALIZE_AS_OBJECT]) &&
//            $context[DateTimeNormalizer::NORMALIZE_AS_OBJECT]
//        ) {
//            return $object;
//        }

        return parent::normalize($object, $format, $context);
    }
}
