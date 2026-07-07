<?php

namespace Src\Domain\Tenant\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class PluginInstalledEvent extends DomainEvent
{
    public function __construct(
        public readonly string $pluginId,
        public readonly string $tenantId,
        public readonly string $pluginSlug,
        public readonly string $version
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'plugin.installed';
    }
}
