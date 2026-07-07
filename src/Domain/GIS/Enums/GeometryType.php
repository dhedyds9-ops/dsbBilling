<?php

namespace Src\Domain\GIS\Enums;

enum GeometryType: string {
    case POINT = 'Point';
    case LINESTRING = 'LineString';
    case POLYGON = 'Polygon';
    case MULTIPOINT = 'MultiPoint';
    case MULTILINESTRING = 'MultiLineString';
    case MULTIPOLYGON = 'MultiPolygon';
    case GEOMETRYCOLLECTION = 'GeometryCollection';
}
