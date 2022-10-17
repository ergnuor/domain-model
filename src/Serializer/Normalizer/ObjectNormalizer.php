<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Serializer\Normalizer;

/**
 * Существует проблема, что базовый сериализатор Symfony
 * в конструкторе устанавливает самого себя для интерфейсов вида {@see \Symfony\Component\Serializer\SerializerAwareInterface}.
 *
 * В конфигурации новых сериализаторов были переиспользованы базовые нормализаторы экземпляр которых разделяется между сериализаторами.
 * Это ведет к тому, что нормализаторы получают неопределенные сериализаторы в качестве зависимости
 * - какой именно сериализатор он получит зависит от последовательности инстанцирования сериализаторов.
 *
 * См. конструктор {@see \Symfony\Component\Serializer\Serializer}.
 * Поэтому проблема была решена путем создания отдельных нормализаторов под каждый сериализатор.
 *
 * UPD: вообще в итоге еще был создан кастомный базовый нормализатор @see \Ergnuor\DomainModel\Serializer\Normalizer\BaseObjectNormalizer
 */
class ObjectNormalizer extends \Ergnuor\DomainModel\Serializer\Normalizer\BaseObjectNormalizer
{
}
