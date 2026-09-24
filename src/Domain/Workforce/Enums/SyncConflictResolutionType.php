<?php

namespace Src\Domain\Workforce\Enums;

enum SyncConflictResolutionType: string {
    case SERVER_WINS = 'server_wins';
    case CLIENT_WINS = 'client_wins';
    case MANUAL = 'manual';
    case MERGE = 'merge';
}
