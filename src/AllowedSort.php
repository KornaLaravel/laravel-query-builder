<?php

namespace Spatie\QueryBuilder;

use Spatie\QueryBuilder\Enums\SortDirection;
use Spatie\QueryBuilder\Exceptions\InvalidDirection;
use Spatie\QueryBuilder\Sorts\Sort;
use Spatie\QueryBuilder\Sorts\SortsCallback;
use Spatie\QueryBuilder\Sorts\SortsField;

class AllowedSort
{
    protected string $defaultDirection;

    protected string $internalName;

    public function __construct(protected string $name, protected Sort $sortClass, ?string $internalName = null)
    {
        $this->name = ltrim($name, '-');

        $this->defaultDirection = static::parseSortDirection($name);

        $this->internalName = $internalName ?? $this->name;
    }

    public static function parseSortDirection(string $name): string
    {
        return str_starts_with($name, '-') ? SortDirection::DESCENDING : SortDirection::ASCENDING;
    }

    public function sort(QueryBuilder $query, ?bool $descending = null): void
    {
        $descending = $descending ?? ($this->defaultDirection === SortDirection::DESCENDING);

        ($this->sortClass)($query->getEloquentBuilder(), $descending, $this->internalName);
    }

    public static function field(string $name, ?string $internalName = null): self
    {
        return new static($name, new SortsField(), $internalName);
    }

    public static function custom(string $name, Sort $sortClass, ?string $internalName = null): self
    {
        return new static($name, $sortClass, $internalName);
    }

    public static function callback(string $name, $callback, ?string $internalName = null): self
    {
        return new static($name, new SortsCallback($callback), $internalName);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSort(string $sortName): bool
    {
        return $this->name === $sortName;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function defaultDirection(string $defaultDirection): static
    {
        if (! in_array($defaultDirection, [
            SortDirection::ASCENDING,
            SortDirection::DESCENDING,
        ])) {
            throw InvalidDirection::make($defaultDirection);
        }

        $this->defaultDirection = $defaultDirection;

        return $this;
    }
}
