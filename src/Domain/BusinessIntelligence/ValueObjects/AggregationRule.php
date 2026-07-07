<?php

namespace Src\Domain\BusinessIntelligence\ValueObjects;

class AggregationRule
{
    public const FUNC_SUM = 'sum';
    public const FUNC_AVG = 'avg';
    public const FUNC_COUNT = 'count';
    public const FUNC_MIN = 'min';
    public const FUNC_MAX = 'max';
    public const FUNC_COUNT_DISTINCT = 'count_distinct';
    public const FUNC_MEDIAN = 'median';
    public const FUNC_STD_DEV = 'std_dev';
    public const FUNC_PERCENTILE = 'percentile';
    public const FUNC_FIRST = 'first';
    public const FUNC_LAST = 'last';

    public function __construct(
        public readonly string $field,
        public readonly string $function,
        public readonly ?string $alias = null,
        public readonly ?array $parameters = null
    ) {
        $validFunctions = [
            self::FUNC_SUM,
            self::FUNC_AVG,
            self::FUNC_COUNT,
            self::FUNC_MIN,
            self::FUNC_MAX,
            self::FUNC_COUNT_DISTINCT,
            self::FUNC_MEDIAN,
            self::FUNC_STD_DEV,
            self::FUNC_PERCENTILE,
            self::FUNC_FIRST,
            self::FUNC_LAST,
        ];

        if (!in_array($function, $validFunctions)) {
            throw new \InvalidArgumentException("Invalid aggregation function: {$function}");
        }
    }

    public static function sum(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_SUM, $alias ?? "sum_{$field}");
    }

    public static function avg(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_AVG, $alias ?? "avg_{$field}");
    }

    public static function count(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_COUNT, $alias ?? "count_{$field}");
    }

    public static function min(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_MIN, $alias ?? "min_{$field}");
    }

    public static function max(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_MAX, $alias ?? "max_{$field}");
    }

    public static function countDistinct(string $field, ?string $alias = null): self
    {
        return new self($field, self::FUNC_COUNT_DISTINCT, $alias ?? "count_distinct_{$field}");
    }

    public static function percentile(string $field, int $percentile, ?string $alias = null): self
    {
        return new self($field, self::FUNC_PERCENTILE, $alias ?? "p{$percentile}_{$field}", ['percentile' => $percentile]);
    }

    public function getAlias(): string
    {
        return $this->alias ?? "{$this->function}_{$this->field}";
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'function' => $this->function,
            'alias' => $this->getAlias(),
            'parameters' => $this->parameters,
        ];
    }
}
