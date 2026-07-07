<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

class FilterCriteria
{
    public const OPERATOR_EQUALS = 'equals';
    public const OPERATOR_NOT_EQUALS = 'not_equals';
    public const OPERATOR_GREATER_THAN = 'gt';
    public const OPERATOR_GREATER_THAN_OR_EQUAL = 'gte';
    public const OPERATOR_LESS_THAN = 'lt';
    public const OPERATOR_LESS_THAN_OR_EQUAL = 'lte';
    public const OPERATOR_IN = 'in';
    public const OPERATOR_NOT_IN = 'not_in';
    public const OPERATOR_BETWEEN = 'between';
    public const OPERATOR_LIKE = 'like';
    public const OPERATOR_NOT_LIKE = 'not_like';
    public const OPERATOR_IS_NULL = 'is_null';
    public const OPERATOR_IS_NOT_NULL = 'is_not_null';

    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value,
        public readonly ?string $logic = 'AND'
    ) {}

    public static function equals(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_EQUALS, $value);
    }

    public static function notEquals(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_NOT_EQUALS, $value);
    }

    public static function greaterThan(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_GREATER_THAN, $value);
    }

    public static function gte(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_GREATER_THAN_OR_EQUAL, $value);
    }

    public static function lessThan(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_LESS_THAN, $value);
    }

    public static function lte(string $field, mixed $value): self
    {
        return new self($field, self::OPERATOR_LESS_THAN_OR_EQUAL, $value);
    }

    public static function in(string $field, array $values): self
    {
        return new self($field, self::OPERATOR_IN, $values);
    }

    public static function notIn(string $field, array $values): self
    {
        return new self($field, self::OPERATOR_NOT_IN, $values);
    }

    public static function between(string $field, mixed $min, mixed $max): self
    {
        return new self($field, self::OPERATOR_BETWEEN, [$min, $max]);
    }

    public static function like(string $field, string $pattern): self
    {
        return new self($field, self::OPERATOR_LIKE, $pattern);
    }

    public static function isNull(string $field): self
    {
        return new self($field, self::OPERATOR_IS_NULL, null);
    }

    public static function isNotNull(string $field): self
    {
        return new self($field, self::OPERATOR_IS_NOT_NULL, null);
    }

    public function and(self $other): FilterGroup
    {
        return new FilterGroup([$this, $other], 'AND');
    }

    public function or(self $other): FilterGroup
    {
        return new FilterGroup([$this, $other], 'OR');
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
            'logic' => $this->logic,
        ];
    }
}

class FilterGroup
{
    /**
     * @param FilterCriteria[] $filters
     */
    public function __construct(
        public readonly array $filters,
        public readonly string $logic = 'AND'
    ) {}

    public function add(FilterCriteria $filter): self
    {
        return new self(array_merge($this->filters, [$filter]), $this->logic);
    }

    public function addAnd(FilterCriteria $filter): self
    {
        $newFilters = $this->filters;
        $newFilters[] = $filter;
        return new self($newFilters, 'AND');
    }

    public function addOr(FilterCriteria $filter): self
    {
        return new self(array_merge($this->filters, [$filter]), 'OR');
    }

    public function toArray(): array
    {
        return [
            'logic' => $this->logic,
            'filters' => array_map(fn($f) => $f->toArray(), $this->filters),
        ];
    }
}
