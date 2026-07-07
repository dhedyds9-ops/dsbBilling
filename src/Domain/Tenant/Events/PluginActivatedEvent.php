<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PluginActivatedEvent extends DomainEvent
{
    public function __construct(
        public readonly string $pluginId,
        public readonly string $tenantId,
        public readonly string $pluginSlug
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'plugin.activated';
    }
}
