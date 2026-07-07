<?php

namespace Src\Domain\Workforce\Enums;

enum SyncTaskType: string {
    case UPLOAD = 'upload';
    case DOWNLOAD = 'download';
}
