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
                if (is_string($id) && str_starts_with($id, 'onu-')) {
                    $onuId = substr($id, 4);
                    $model = \App\Models\ISP\Onu::find($onuId);
                } else {
                    $model = Customer::find($id);
                    if ($model) {
                        $cs = $model->customerServices()->first();
                        if ($cs && $cs->onu) {
                            $cs->onu->latitude = $lat;
                            $cs->onu->longitude = $lng;
                            $cs->onu->save();
                        }
                    }
                }
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
                    if ($request->has('name')) $model->name = $request->input('name');
                    $baseCode = $request->input('code') ?: ($model->code ?: ('OLT-' . strtoupper(\Illuminate\Support\Str::slug($request->input('name', 'NODE'), ''))));
                    $code = $baseCode;
                    $counter = 1;
                    while (Olt::where('code', $code)->where('id', '!=', $id ?? 0)->exists()) {
                        $code = $baseCode . '-' . $counter++;
                    }
                    $model->code = $code;
                    $ip = $request->input('ip_address') ?: $request->input('host');
                    if ($ip) {
                        $model->ip_address = $ip;
                        $model->host = $ip;
                    }
                    if ($request->has('pon_port_count')) {
                        $model->pon_port_count = (int)$request->input('pon_port_count');
                        $model->port_count = (int)$request->input('pon_port_count');
                    }
                    if ($request->has('model')) $model->model = $request->input('model');
                    if ($request->has('address')) $model->address = $request->input('address');
                    if ($request->has('description')) $model->description = $request->input('description');
                    if ($request->has('status')) $model->status = $request->input('status');
                    if ($request->has('port')) $model->port = $request->input('port') ?: ($model->port ?? 23);
                    if ($request->has('community')) $model->community = $request->input('community') ?: ($model->community ?? 'public');
                    if ($request->has('snmp_port')) $model->snmp_port = $request->input('snmp_port') ?: ($model->snmp_port ?? 161);
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
            case 'customer':
                if (is_string($id) && str_starts_with($id, 'onu-')) {
                    $onuId = substr($id, 4);
                    $onu = \App\Models\ISP\Onu::find($onuId);
                    if ($onu) {
                        if ($request->has('name')) $onu->name = $request->input('name');
                        if ($request->has('serial_number')) $onu->serial_number = $request->input('serial_number');
                        if ($request->has('odp_id')) $onu->odp_id = $request->input('odp_id') ?: null;
                        if ($request->has('latitude')) $onu->latitude = $request->input('latitude');
                        if ($request->has('longitude')) $onu->longitude = $request->input('longitude');
                        $onu->save();
                    }
                    $model = $onu;
                } else {
                    $model = $id ? Customer::find($id) : new Customer();
                    if ($model) {
                        if ($request->has('name')) $model->name = $request->input('name');
                        if ($request->has('phone')) $model->phone = $request->input('phone');
                        if ($request->has('address')) $model->address = $request->input('address');
                        if ($request->has('code') && !empty($request->input('code'))) {
                            $model->code = $request->input('code');
                        } elseif (!$id) {
                            $model->code = \App\Services\CRM\CustomerCodeGenerator::generate();
                        }
                        if (!$id) {
                            $model->status = 'active';
                            $model->created_by = auth()->id() ?? 1;
                        }
                        $model->updated_by = auth()->id() ?? 1;
                        if ($request->has('latitude')) $model->latitude = $request->input('latitude');
                        if ($request->has('longitude')) $model->longitude = $request->input('longitude');
                        $model->save();

                        // Hubungkan/perbarui data ONU dan ODP
                        $cs = $model->customerServices()->first();
                        $odpId = $request->input('odp_id');
                        $onuSn = $request->input('serial_number');
                        if ($cs) {
                            $onu = $cs->onu;
                            if ($onu) {
                                if ($odpId !== null) $onu->odp_id = $odpId ?: null;
                                if ($onuSn) $onu->serial_number = $onuSn;
                                if ($request->has('latitude')) $onu->latitude = $request->input('latitude');
                                if ($request->has('longitude')) $onu->longitude = $request->input('longitude');
                                $onu->save();
                            }
                        }
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
        if (is_string($id) && str_starts_with($id, 'onu-')) {
            $onu = \App\Models\ISP\Onu::find(substr($id, 4));
            $isOnline = $onu?->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 10;
            return response()->json([
                'success' => true,
                'is_online' => $isOnline,
                'total_clients' => 0,
                'ssid_name' => $onu?->wifi_ssid ?: 'N/A',
                'ssid_password' => $onu?->wifi_password ?: '',
                'tr069_ip' => $onu?->serial_number ? 'TR069-' . $onu->serial_number : '-',
                'last_inform' => $onu?->last_seen_at,
                'rx_power' => $onu?->rx_power_dbm ? $onu->rx_power_dbm . ' dBm' : null,
                'rdm_power' => $onu?->tx_power_dbm ? $onu->tx_power_dbm . ' dBm' : null,
            ]);
        }

        $customer = Customer::with(['customerServices.onu', 'customerServices.acsDevice'])->find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $cs = $customer->customerServices->first();
        $onu = $cs?->onu;
        $acsDevice = $cs?->acsDevice ?? $onu?->acsDevice;

        $isOnline = false;
        $totalClients = 0;
        $ssidName = $onu?->wifi_ssid ?: 'N/A';
        $ssidPassword = $onu?->wifi_password ?: '';
        $tr069Ip = $acsDevice?->ip_address ?: ($cs?->tr069_status ?: ($onu?->serial_number ? 'TR069-' . $onu->serial_number : '-'));
        $lastInform = $acsDevice?->last_inform ?: ($onu?->last_seen_at ?: $cs?->last_seen_at);
        $rxPower = $onu?->rx_power_dbm ? $onu->rx_power_dbm . ' dBm' : null;
        $rdmPower = $onu?->tx_power_dbm ? $onu->tx_power_dbm . ' dBm' : null;

        if ($acsDevice) {
            $isOnline = $acsDevice->status === 'online' || ($acsDevice->last_inform && $acsDevice->last_inform->diffInMinutes(now()) <= 10);
        } elseif ($onu && $onu->last_seen_at) {
            $isOnline = $onu->last_seen_at->diffInMinutes(now()) <= 10;
        } elseif ($cs) {
            $isOnline = $cs->status === 'active';
        }

        if (class_exists(\App\Services\ISP\GenieAcsService::class) && $onu) {
            try {
                $genieService = app(\App\Services\ISP\GenieAcsService::class);
                $wifiInfo = $genieService->getWifiCredentials($customer->id, $onu->id);
                if (!empty($wifiInfo['ssid'])) $ssidName = $wifiInfo['ssid'];
                if (!empty($wifiInfo['password'])) $ssidPassword = $wifiInfo['password'];
                if (!empty($wifiInfo['online'])) $isOnline = true;
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'is_online' => $isOnline,
            'total_clients' => $totalClients,
            'ssid_name' => $ssidName,
            'ssid_password' => $ssidPassword,
            'tr069_ip' => $tr069Ip,
            'last_inform' => $lastInform,
            'rx_power' => $rxPower,
            'rdm_power' => $rdmPower,
        ]);
    }

    public function wlanUpdate(Request $request, $id)
    {
        $ssid = $request->input('ssid');
        $password = $request->input('password');

        if (is_string($id) && str_starts_with($id, 'onu-')) {
            $onu = \App\Models\ISP\Onu::find(substr($id, 4));
            if ($onu) {
                $onu->wifi_ssid = $ssid;
                $onu->wifi_password = $password;
                $onu->save();
            }
            return response()->json(['success' => true, 'message' => 'Pengaturan WiFi berhasil diperbarui']);
        }

        $customer = Customer::with(['customerServices.onu'])->find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $cs = $customer->customerServices->first();
        $onu = $cs?->onu;
        if ($onu) {
            $onu->wifi_ssid = $ssid;
            $onu->wifi_password = $password;
            $onu->save();
        }

        if (class_exists(\App\Services\ISP\GenieAcsService::class) && $onu) {
            try {
                $genieService = app(\App\Services\ISP\GenieAcsService::class);
                $genieService->setWifiCredentials($customer->id, $onu->id, $ssid, $password);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[wlanUpdate] GenieACS push notice: ' . $e->getMessage());
            }
        }

        \Illuminate\Support\Facades\Cache::forget('gis_map_data');
        \Illuminate\Support\Facades\Cache::forget('gis_map_nodes');

        return response()->json(['success' => true, 'message' => 'Pengaturan WiFi berhasil diperbarui']);
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
