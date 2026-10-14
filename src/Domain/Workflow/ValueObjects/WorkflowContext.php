<?php

namespace Src\Domain\Workflow\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

readonly class WorkflowContext implements JsonSerializable
{
    public function __construct(
        public array $data = [],
        public ?string $triggerType = null,
        public ?string $triggerSource = null,
        public ?string $initiatorId = null,
        public ?string $initiatorType = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: $data['data'] ?? [],
            triggerType: $data['trigger_type'] ?? null,
            triggerSource: $data['trigger_source'] ?? null,
            initiatorId: $data['initiator_id'] ?? null,
            initiatorType: $data['initiator_type'] ?? null
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function with(string $key, mixed $value): self
    {
        return new self(
            data: array_merge($this->data, [$key => $value]),
            triggerType: $this->triggerType,
            triggerSource: $this->triggerSource,
            initiatorId: $this->initiatorId,
            initiatorType: $this->initiatorType
        );
    }

    public function withContext(array $additionalData): self
    {
        return new self(
            data: array_merge($this->data, $additionalData),
            triggerType: $this->triggerType,
            triggerSource: $this->triggerSource,
            initiatorId: $this->initiatorId,
            initiatorType: $this->initiatorType
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'trigger_type' => $this->triggerType,
            'trigger_source' => $this->triggerSource,
            'initiator_id' => $this->initiatorId,
            'initiator_type' => $this->initiatorType,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
