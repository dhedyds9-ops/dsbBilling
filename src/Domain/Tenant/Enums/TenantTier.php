<?php

namespace Src\Domain\Tenant\Enums;

enum TenantTier: string
{
    case STARTER = 'starter';
    case PROFESSIONAL = 'professional';
    case ENTERPRISE = 'enterprise';
    case UNLIMITED = 'unlimited';
}
