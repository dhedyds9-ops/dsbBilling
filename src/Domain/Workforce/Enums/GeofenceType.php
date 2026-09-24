<?php

namespace Src\Domain\Workforce\Enums;

enum GeofenceType: string {
    case CIRCLE = 'circle';
    case POLYGON = 'polygon';
    case RECTANGLE = 'rectangle';
}
