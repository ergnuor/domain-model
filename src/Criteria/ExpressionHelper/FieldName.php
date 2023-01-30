<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\ExpressionHelper;


class FieldName
{
    private string $name;
    private bool $isInverted;
    private string $suffix;

    private function __construct(string $name, bool $isInverted, ?string $suffix)
    {
        $this->name = $name;
        $this->isInverted = $isInverted;
        $this->suffix = (string)$suffix;
    }

    public static function fromEncodedFieldName(string $fieldName): self
    {
        $fieldName = preg_split('/@(?=[^@]*$)/', $fieldName)[0];
        $realFieldName = self::getFieldNameWithoutModifier($fieldName);
        $isInverted = self::isHasInversionModifier($fieldName);
        $suffix = self::getFieldSuffix($fieldName);

        return new self($realFieldName, $isInverted, $suffix);
    }

    public static function getFieldNameWithoutModifier(string $filterName): string
    {
        $filterName = trim($filterName, '!');

        return explode(':', $filterName)[0];
    }

    public static function isHasInversionModifier(string $filterName): bool
    {
        return $filterName[0] == '!';
    }

    public static function getFieldSuffix(string $filterName): ?string
    {
        return explode(':', $filterName)[1] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInverted(): bool
    {
        return $this->isInverted;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function isHasSuffix(): bool
    {
        return !empty($this->suffix);
    }

    public function changeName(string $name): self
    {
        return new self(
            $name,
            $this->isInverted(),
            $this->getSuffix()
        );
    }

    public function changeInversion(bool $isInverted): self
    {
        return new self(
            $this->getName(),
            $isInverted,
            $this->getSuffix()
        );
    }

    public function toString(): string
    {
        $stringFilterName = '';

        if ($this->isInverted()) {
            $stringFilterName .= '!';
        }

        $stringFilterName .= $this->getName();

        if ($this->isHasSuffix()) {
            $stringFilterName .= ':' . $this->getSuffix();
        }

        return $stringFilterName;
    }
}