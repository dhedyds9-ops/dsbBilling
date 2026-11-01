<?php

namespace App\Http\Controllers\Gis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\Customer;
use App\Models\ISP\Olt;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\DistributionBox;
use App\Models\ISP\JointClosure;

class MapApiController extends Controller
{
    public function getConnections()
    {
        // For now, return empty or implement actual logic if a MapConnection model exists
        return response()->json([]);
    }

    public function saveConnection(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function updateLocation(Request $request, $type, $id)
    {
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $model = null;
        switch ($type) {
            case 'olt':
                $model = Olt::find($id);
                break;
            case 'odc':
                $model = Odc::find($id);
                break;
            case 'odp':
                $model = Odp::find($id);
                break;
            case 'htb':
                if (class_exists(DistributionBox::class)) $model = DistributionBox::find($id);
                break;
            case 'closure':
                if (class_exists(JointClosure::class)) $model = JointClosure::find($id);
                break;
            case 'customer':
                $model = Customer::find($id);
                break;
        }

        if ($model) {
            $model->latitude = $lat;
            $model->longitude = $lng;
            $model->save();

            \Illuminate\Support\Facades\Cache::forget('gis_map_data');
            \Illuminate\Support\Facades\Cache::forget('gis_map_nodes');

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Model not found'], 404);
    }

    public function storeNode(Request $request, $type)
    {
        return $this->saveNodeData($request, $type, null);
    }

    public function updateNode(Request $request, $type, $id)
    {
        return $this->saveNodeData($request, $type, $id);
    }

    private function saveNodeData(Request $request, $type, $id)
    {
        $model = null;
        switch ($type) {
            case 'olt':
                $model = $id ? Olt::find($id) : new Olt();
                if ($model) {
                    $model->name = $request->input('name');
                    $baseCode = $request->input('code') ?: ('OLT-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), '')));
                    $code = $baseCode;
                    $counter = 1;
                    while (Olt::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                        $code = $baseCode . '-' . $counter++;
                    }
                    $model->code = $code;
                    $model->host = $request->input('host') ?: ($model->host ?? '127.0.0.1');
                    $model->port = $request->input('port') ?: ($model->port ?? 23);
                    $model->community = $request->input('community') ?: ($model->community ?? 'public');
                    $model->snmp_port = $request->input('snmp_port') ?: ($model->snmp_port ?? 161);
                }
                break;
            case 'odc':
                $model = $id ? Odc::find($id) : new Odc();
                if ($model) {
                    $model->name = $request->input('name');
                    $baseCode = $request->input('code') ?: ('ODC-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), '')));
                    $code = $baseCode;
                    $counter = 1;
                    while (Odc::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                        $code = $baseCode . '-' . $counter++;
                    }
                    $model->code = $code;
                    $model->address = $request->input('address');
                    $model->description = $request->input('description');
                    $model->port_count = $request->input('port_count') ?: 144;
                    if ($request->has('olt_id')) $model->olt_id = $request->input('olt_id');
                }
                break;
            case 'odp':
                $model = $id ? Odp::find($id) : new Odp();
                if ($model) {
                    $model->name = $request->input('name');
                    $baseCode = $request->input('code') ?: ('ODP-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), '')));
                    $code = $baseCode;
                    $counter = 1;
                    while (Odp::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                        $code = $baseCode . '-' . $counter++;
                    }
                    $model->code = $code;
                    $model->address = $request->input('address');
                    $model->description = $request->input('description');
                    $model->port_count = $request->input('port_count') ?: 8;
                    $model->split_ratio = $request->input('split_ratio') ?: 8;
                    if ($request->has('odc_id')) $model->odc_id = $request->input('odc_id');
                }
                break;
            case 'htb':
                if (class_exists(DistributionBox::class)) {
                    $model = $id ? DistributionBox::find($id) : new DistributionBox();
                    if ($model) {
                        $model->name = $request->input('name');
                        $baseCode = $request->input('code') ?: ('HTB-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), '')));
                        $code = $baseCode;
                        $counter = 1;
                        while (DistributionBox::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                            $code = $baseCode . '-' . $counter++;
                        }
                        $model->code = $code;
                        $model->address = $request->input('address');
                        $model->description = $request->input('description');
                        $model->port_count = $request->input('port_count') ?: 8;
                        if ($request->has('odp_id')) $model->odp_id = $request->input('odp_id') ?: null;
                    }
                }
                break;
            case 'closure':
                if (class_exists(JointClosure::class)) {
                    $model = $id ? JointClosure::find($id) : new JointClosure();
                    if ($model) {
                        $model->name = $request->input('name');
                        $baseCode = $request->input('code') ?: ('JC-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), '')));
                        $code = $baseCode;
                        $counter = 1;
                        while (JointClosure::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                            $code = $baseCode . '-' . $counter++;
                        }
                        $model->code = $code;
                        $model->address = $request->input('address');
                        $model->description = $request->input('description');
                        if ($request->has('odc_id')) $model->odc_id = $request->input('odc_id');
                    }
                }
                break;
        }

        if ($model) {
            // Map common inputs
            if ($request->has('latitude')) $model->latitude = $request->input('latitude');
            if ($request->has('longitude')) $model->longitude = $request->input('longitude');
            
            $model->save();

            \Illuminate\Support\Facades\Cache::forget('gis_map_data');
            \Illuminate\Support\Facades\Cache::forget('gis_map_nodes');

            return response()->json(['success' => true, 'id' => $model->id]);
        }

        return response()->json(['success' => false, 'message' => 'Model not found or saving failed'], 404);
    }

    public function wlanStatus($id)
    {
        // Mock response for now, in real life this queries TR069
        return response()->json([
            'success' => true,
            'total_clients' => 0,
            'ssid_name' => 'WIFI-' . $id,
            'is_online' => true,
            'tr069_ip' => '10.0.0.' . $id
        ]);
    }

    public function wlanUpdate(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function ping(Request $request)
    {
        return response()->json([
            'success' => true,
            'output' => "PING " . $request->target . "\nReply from " . $request->target . ": time=1ms"
        ]);
    }

    public function onlinePaths()
    {
        return response()->json(['paths' => []]);
    }
}
