<?php

namespace Src\Domain\GIS\Enums;

enum CoordinateSystem: string {
    case WGS84 = 'WGS84';
    case EPSG4326 = 'EPSG:4326';
    case EPSG3857 = 'EPSG:3857';
    case UTM = 'UTM';
}
