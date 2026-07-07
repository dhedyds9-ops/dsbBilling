<?php

namespace Src\Domain\Tenant\Enums;

enum PluginStatus: string
{
    case INSTALLED = 'installed';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case UPDATE_AVAILABLE = 'update_available';
    case UNINSTALLED = 'uninstalled';
}
