<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class CollectionNormalizer implements SerializerAwareInterface, NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_a($type, \Doctrine\Common\Collections\ArrayCollection::class, true);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof \Doctrine\Common\Collections\ArrayCollection;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return new \Doctrine\Common\Collections\ArrayCollection($data);
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $object
     * @param string|null $format
     * @param array $context
     * @return array|null
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return $object->map(function ($item) use ($object, $format, $context) {
            if (!$this->serializer instanceof NormalizerInterface) {
                throw new LogicException(sprintf('Cannot normalize collection "%s" item "%s" because the injected serializer is not a normalizer.',
                    get_debug_type($object), get_debug_type($item)));
            }

            return $this->serializer->normalize($item, $format, $context);
        })->toArray();
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
