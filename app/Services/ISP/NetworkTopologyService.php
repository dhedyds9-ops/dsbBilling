<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Odp;
use App\Models\ISP\Splitter;

class NetworkTopologyService
{
    public function getCustomerPath(Onu $onu): array
    {
        $path = [];

        if ($onu->splitter) {
            $path['splitter'] = $onu->splitter;

            if ($onu->splitter->odp) {
                $path['odp'] = $onu->splitter->odp;

                if ($onu->splitter->odp->odc) {
                    $path['odc'] = $onu->splitter->odp->odc;
                }
            }
        }

        if ($onu->ponPort) {
            $path['pon_port'] = $onu->ponPort;
        }

        if ($onu->olt) {
            $path['olt'] = $onu->olt;

            if ($onu->olt->pop) {
                $path['pop'] = $onu->olt->pop;

                if ($onu->olt->pop->tower) {
                    $path['tower'] = $onu->olt->pop->tower;
                }
            }
        }

        $path['onu'] = $onu;

        return $path;
    }

    public function calculateOdpCapacity(Odp $odp): array
    {
        $totalPorts = $odp->port_count;
        $usedPorts = $odp->active_port_count;
        $availablePorts = $totalPorts - $usedPorts;
        $utilization = $totalPorts > 0 ? ($usedPorts / $totalPorts) * 100 : 0;

        return [
            'total' => $totalPorts,
            'used' => $usedPorts,
            'available' => $availablePorts,
            'utilization' => round($utilization, 2),
        ];
    }

    public function calculateSplitterCapacity(Splitter $splitter): array
    {
        $totalPorts = $splitter->port_count;
        $usedPorts = $splitter->active_port_count;
        $availablePorts = $totalPorts - $usedPorts;
        $utilization = $totalPorts > 0 ? ($usedPorts / $totalPorts) * 100 : 0;

        return [
            'total' => $totalPorts,
            'used' => $usedPorts,
            'available' => $availablePorts,
            'utilization' => round($utilization, 2),
        ];
    }

    public function calculateOltPortAvailability(Olt $olt): array
    {
        $totalPorts = $olt->port_count;
        $usedPorts = $olt->active_port_count;
        $availablePorts = $totalPorts - $usedPorts;
        $utilization = $totalPorts > 0 ? ($usedPorts / $totalPorts) * 100 : 0;

        return [
            'total' => $totalPorts,
            'used' => $usedPorts,
            'available' => $availablePorts,
            'utilization' => round($utilization, 2),
        ];
    }

    public function getParentChildTree($model): array
    {
        $tree = [
            'model' => $model,
            'children' => [],
        ];

        if ($model instanceof Pop) {
            $tree['children']['olts'] = $model->olts()->with('ponPorts')->get();
            $tree['children']['odcs'] = $model->odcs()->with('odps.splitters')->get();
            $tree['children']['routers'] = $model->routers()->get();
            $tree['children']['switches'] = $model->switches()->get();
            $tree['children']['racks'] = $model->racks()->with('patchPanels')->get();
        } elseif ($model instanceof Olt) {
            $tree['children']['pon_ports'] = $model->ponPorts()->with('onus')->get();
            $tree['children']['odcs'] = $model->odcs()->with('odps.splitters')->get();
        } elseif ($model instanceof Odp) {
            $tree['children']['splitters'] = $model->splitters()->with('onus')->get();
        }

        return $tree;
    }
}
