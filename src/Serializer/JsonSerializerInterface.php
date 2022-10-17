<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer;

interface JsonSerializerInterface
{
    /**
     * @param $data
     * @param array $context
     * @return array|string|int|float|bool|\ArrayObject|null \ArrayObject is used to make sure an empty object is encoded as an object not an array
     */
    public function normalize($data, array $context = []);

    /**
     * @param $data
     * @param string $type
     * @param array $context
     * @return mixed
     */
    public function denormalize($data, string $type, array $context = []);
}