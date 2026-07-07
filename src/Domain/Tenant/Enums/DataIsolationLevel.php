<?php

namespace Src\Domain\Tenant\Enums;

enum DataIsolationLevel: string
{
    case SHARED = 'shared';
    case ISOLATED = 'isolated';
    case HYBRID = 'hybrid';
}
