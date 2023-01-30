<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionMapper;

class
Identifiers
{
    /** @var array<string, int> */
    private array $baseNameCount = [];

    public function getNext(string $baseName): string
    {
        $baseName = $this->normalizeBaseName($baseName);

        $this->baseNameCount[$baseName] = $this->baseNameCount[$baseName] ?? 0;

        $nextParameterIndex = $this->baseNameCount[$baseName]++;

        return $baseName . '_' . $nextParameterIndex;
    }

    private function normalizeBaseName(string $baseName): string
    {
        return preg_replace('/[^\w]+/', '_', $baseName);
    }
}