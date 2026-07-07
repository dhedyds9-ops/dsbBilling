<?php

namespace Src\Domain\Workforce\Enums;

enum QCResultStatus: string {
    case PASS = 'pass';
    case FAIL = 'fail';
    case NA = 'na';
}
