<?php

namespace Src\Domain\Workflow\ValueObjects;

use InvalidArgumentException;

readonly class TransitionCondition
{
    public function __construct(
        public string $field,
        public string $operator,
        public mixed $value
    ) {
        if (!in_array($operator, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'not_in', 'contains', 'not_contains', 'is_null', 'is_not_null'])) {
            throw new InvalidArgumentException("Invalid operator: {$operator}");
        }
    }

    public static function equals(string $field, mixed $value): self
    {
        return new self($field, 'eq', $value);
    }

    public static function notEquals(string $field, mixed $value): self
    {
        return new self($field, 'neq', $value);
    }

    public static function greaterThan(string $field, mixed $value): self
    {
        return new self($field, 'gt', $value);
    }

    public static function lessThan(string $field, mixed $value): self
    {
        return new self($field, 'lt', $value);
    }

    public static function in(string $field, array $values): self
    {
        return new self($field, 'in', $values);
    }

    public function evaluate(mixed $context): bool
    {
        $fieldValue = is_array($context) ? ($context[$this->field] ?? null) : (is_object($context) ? ($context->{$this->field} ?? null) : null);

        return match($this->operator) {
            'eq' => $fieldValue == $this->value,
            'neq' => $fieldValue != $this->value,
            'gt' => $fieldValue > $this->value,
            'gte' => $fieldValue >= $this->value,
            'lt' => $fieldValue < $this->value,
            'lte' => $fieldValue <= $this->value,
            'in' => is_array($this->value) && in_array($fieldValue, $this->value),
            'not_in' => is_array($this->value) && !in_array($fieldValue, $this->value),
            'contains' => is_string($fieldValue) && str_contains($fieldValue, $this->value),
            'not_contains' => is_string($fieldValue) && !str_contains($fieldValue, $this->value),
            'is_null' => $fieldValue === null,
            'is_not_null' => $fieldValue !== null,
            default => false
        };
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }
}
