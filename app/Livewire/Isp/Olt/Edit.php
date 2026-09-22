<?php

namespace App\Livewire\Isp\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt as OltModel;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\OltService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $oltId;
    public $olt;
    public $pop_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $firmware_version;
    public $ip_address;

    public $latitude;
    public $longitude;
    public $address;
    public $host;

    public $port_count = 0;
    public $pon_port_count = 0;
    public $onu_capacity = 64;

    public $status = 'active';

    public $snmp_version = '2c';
    public $snmp_port;
    public $snmp_community_read = 'public';
    public $snmp_community_write = 'private';

    public $cli_mode = 'telnet';
    public $cli_port = 23;
    public $username;
    public $password;
    public $enable_secret;

    public $detectedInfo = null;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'olts';
        $this->oltId = $id;
        $this->olt = OltModel::findOrFail($id);

        $this->pop_id = $this->olt->pop_id;
        $this->vendor_id = $this->olt->vendor_id;
        $this->code = $this->olt->code;
        $this->name = $this->olt->name;
        $this->description = $this->olt->description;
        $this->model = $this->olt->model;
        $this->serial_number = $this->olt->serial_number;
        $this->firmware_version = $this->olt->firmware_version;
        $this->ip_address = $this->olt->ip_address;

        $this->latitude = $this->olt->latitude;
        $this->longitude = $this->olt->longitude;
        $this->address = $this->olt->address;
        $this->host = $this->olt->host;

        $this->port_count = $this->olt->port_count ?? 0;
        $this->pon_port_count = $this->olt->pon_port_count ?? 0;
        $this->onu_capacity = $this->olt->onu_capacity ?? 64;

        $this->snmp_version = $this->olt->snmp_version ?? '2c';
        $this->snmp_port = $this->olt->snmp_port ?? 161;
        $this->snmp_community_read = $this->olt->snmp_community_read;
        $this->snmp_community_write = $this->olt->snmp_community_write;

        $this->cli_mode = $this->olt->cli_mode ?? 'telnet';
        $this->cli_port = $this->olt->cli_port ?? 23;
        $this->username = $this->olt->username;
        $this->password = $this->olt->password;
        $this->enable_secret = $this->olt->enable_secret;

        $this->status = $this->olt->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.olts.index')],
            ['label' => 'OLT', 'url' => route('isp.olts.index')],
            ['label' => $this->olt->name, 'url' => route('isp.olts.show', $this->oltId)],
            ['label' => 'Edit'],
        ];
    }

    public function updatedCliMode()
    {
        if ($this->cli_mode === 'ssh') {
            $this->cli_port = 22;
        } elseif ($this->cli_mode === 'telnet') {
            $this->cli_port = 23;
        }
    }

    public function testConnection()
    {
        $this->validate([
            'ip_address' => 'required|ip',
            'vendor_id' => 'required',
        ], [
            'ip_address.required' => 'IP Address diperlukan untuk test koneksi.',
            'vendor_id.required' => 'Vendor OLT harus dipilih.',
        ]);

        $normalizedSnmpVersion = is_string($this->snmp_version)
            ? preg_replace('/^v/i', '', trim($this->snmp_version))
            : (string)$this->snmp_version;
        if (!in_array($normalizedSnmpVersion, ['1', '2c', '3'], true)) {
            $normalizedSnmpVersion = '2c';
        }
        $this->snmp_version = $normalizedSnmpVersion;

        $ipAddress = trim($this->ip_address);
        $snmpPort = (int)($this->snmp_port ?: 161);
        $communityRead = $this->snmp_community_read === null || $this->snmp_community_read === '' ? 'public' : trim($this->snmp_community_read);

        $dummyOlt = new OltModel([
            'vendor_id' => $this->vendor_id,
            'ip_address' => $ipAddress,
            'snmp_version' => $normalizedSnmpVersion,
            'snmp_port' => $snmpPort,
            'snmp_community_read' => $communityRead,
            'snmp_community_write' => $this->snmp_community_write,
            'cli_mode' => $this->cli_mode,
            'cli_port' => (int)($this->cli_port ?: 23),
            'username' => $this->username,
            'password' => $this->password,
        ]);

        try {
            $driver = $dummyOlt->driver();
            $sysInfo = $driver->getSystemInfo();

            $isOffline = ($sysInfo['status'] ?? 'offline') === 'offline'
                || $sysInfo['uptime'] === 'N/A';

            if ($isOffline) {
                $reason = $sysInfo['error'] ?? 'Tidak ada respons SNMP.';
                $communityHint = $communityRead === 'public' ? ' (Gunakan community default "public")' : " (Community: \"{$communityRead}\")";
                throw new \Exception(
                    "SNMP Timeout/Ditolak. {$reason}" . PHP_EOL
                    . "→ IP: {$ipAddress}:{$snmpPort}, Version: {$normalizedSnmpVersion}{$communityHint}"
                );
            }

            $descr = $sysInfo['description'] ?? '';
            if (empty($this->model)) {
                $this->model = $descr;
            }
            $this->firmware_version = $sysInfo['firmware'] ?? $this->firmware_version;
            $this->detectedInfo = [
                'uptime'   => $sysInfo['uptime'],
                'model'    => $descr ?: ($this->model ?: '-'),
                'firmware' => $sysInfo['firmware'] ?? '-',
                'status'   => 'Online',
            ];
            session()->flash('info', 'Test Koneksi Berhasil! Uptime: ' . $sysInfo['uptime']);
        } catch (\Throwable $th) {
            $this->detectedInfo = null;
            $msg = $th->getMessage();
            if (empty($msg)) {
                $msg = 'Unknown error (exception kosong).';
            }
            $safeMsg = str_replace(["\r", "\n"], ' · ', $msg);
            session()->flash('error', 'Test Koneksi Gagal: ' . $safeMsg);
        }
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:olts,code,' . $this->oltId,
            'name' => 'required|string|max:255',
            'pop_id' => 'nullable|exists:pops,id',
            'ip_address' => 'required|ip',
            'vendor_id' => 'required|exists:vendors,id',
            'status' => 'required|in:active,inactive',
            'snmp_port' => 'required|numeric|min:1|max:65535',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $service = app(OltService::class);
        $service->update($this->olt, [
            'pop_id' => $this->pop_id === '' ? null : $this->pop_id,
            'vendor_id' => $this->vendor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'firmware_version' => $this->firmware_version,
            'ip_address' => $this->ip_address,
            'host' => $this->host,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,

            'port_count' => $this->port_count,
            'pon_port_count' => $this->pon_port_count,
            'onu_capacity' => $this->onu_capacity,

            'snmp_version' => $this->snmp_version,
            'snmp_port' => $this->snmp_port,
            'snmp_community_read' => $this->snmp_community_read,
            'snmp_community_write' => $this->snmp_community_write,

            'cli_mode' => $this->cli_mode,
            'cli_port' => $this->cli_port,
            'username' => $this->username,
            'password' => $this->password,
            'enable_secret' => $this->enable_secret,

            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'OLT berhasil diperbarui!');
        return redirect()->route('isp.olts.show', $this->oltId);
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.olt.edit', compact('pops', 'vendors'));
    }
}


