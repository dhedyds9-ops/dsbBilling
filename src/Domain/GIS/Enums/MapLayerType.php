<?php

namespace Src\Domain\GIS\Enums;

enum MapLayerType: string {
    case BASE = 'base';
    case OVERLAY = 'overlay';
    case HEATMAP = 'heatmap';
    case ROUTE = 'route';
    case AREA = 'area';
}
