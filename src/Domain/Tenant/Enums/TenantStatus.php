<?php

namespace Src\Domain\Tenant\Enums;

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case TERMINATED = 'terminated';
    case TRIAL = 'trial';
}
