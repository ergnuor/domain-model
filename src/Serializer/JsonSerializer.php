<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer;

use Ergnuor\DomainModel\Serializer\JsonSerializerInterface;

class JsonSerializer implements JsonSerializerInterface
{
    private \Symfony\Component\Serializer\Serializer $serializer;

    public function __construct(array $normalizers = [], array $encoders = [])
    {
        $this->serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
    }

    public function normalize($data, array $context = [])
    {
        return $this->serializer->normalize($data, 'json', $context);
    }


    public function denormalize($data, string $type, array $context = [])
    {
        return $this->serializer->denormalize($data, $type, 'json', $context);
    }
}