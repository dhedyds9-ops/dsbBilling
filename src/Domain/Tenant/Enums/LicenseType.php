<?php

namespace Src\Domain\Tenant\Enums;

enum LicenseType: string
{
    case SUBSCRIPTION = 'subscription';
    case PERPETUAL = 'perpetual';
    case USAGE_BASED = 'usage_based';
    case TRIAL = 'trial';
}
