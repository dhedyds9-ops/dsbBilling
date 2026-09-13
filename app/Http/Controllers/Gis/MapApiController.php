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
            case 'odc':
                $model = $id ? Odc::find($id) : new Odc();
                if ($model) {
                    $model->name = $request->input('name');
                    $model->pon_port = $request->input('pon_port');
                    $model->area = $request->input('area');
                    $model->color = $request->input('color');
                    $model->olt_id = $request->input('olt_id');
                    $model->cable_no = $request->input('cable_no');
                }
                break;
            case 'odp':
                $model = $id ? Odp::find($id) : new Odp();
                if ($model) {
                    $model->name = $request->input('name');
                    $model->region_id = $request->input('region_id');
                    $model->odc_id = $request->input('odc_id');
                    $model->color = $request->input('color');
                    $model->kampung = $request->input('kampung');
                    $model->odc_port = $request->input('odc_port');
                }
                break;
            case 'htb':
                if (class_exists(DistributionBox::class)) {
                    $model = $id ? DistributionBox::find($id) : new DistributionBox();
                    if ($model) {
                        $model->name = $request->input('name');
                        $model->uplink_type = $request->input('uplink_type');
                        $model->odp_id = $request->input('odp_id');
                        $model->parent_htb_id = $request->input('parent_htb_id');
                    }
                }
                break;
            case 'closure':
                if (class_exists(JointClosure::class)) {
                    $model = $id ? JointClosure::find($id) : new JointClosure();
                    if ($model) {
                        $model->name = $request->input('name');
                        $model->region_id = $request->input('region_id');
                        $model->odc_id = $request->input('odc_id');
                        $model->description = $request->input('description');
                    }
                }
                break;
        }

        if ($model) {
            // Map common inputs
            if ($request->has('latitude')) $model->latitude = $request->input('latitude');
            if ($request->has('longitude')) $model->longitude = $request->input('longitude');
            if ($request->has('code') && !$model->code) $model->code = $request->input('name');
            
            $model->save();
            return response()->json(['success' => true, 'id' => $model->id]);
        }

        return response()->json(['success' => false, 'message' => 'Failed to save node'], 400);
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
