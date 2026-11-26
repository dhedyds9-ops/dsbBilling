<?php

namespace App\Livewire\ResellerPortal\Network\AcsDevice;

use App\Livewire\AdminComponent;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use App\Services\Auth\ResellerScopeHelper;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public int $perPage = 20;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-acs';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Devices GenieACS', 'url' => '#'],
        ];

        if (request()->query('search')) {
            $this->search = (string) request()->query('search');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function syncDevices(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $driver = app(GenieACSDriver::class);
            $projectionFields = [
                '_id',
                '_deviceId',
                '_ip',
                '_lastInform',
                'VirtualParameters',
                'InternetGatewayDevice.DeviceInfo',
                'Device.DeviceInfo',
                'InternetGatewayDevice.ManagementServer.ConnectionRequestURL',
                'Device.ManagementServer.ConnectionRequestURL',
                'InternetGatewayDevice.WANDevice',
                'InternetGatewayDevice.X_HW_DEBUG.AMP.OpticalInfo',
                'InternetGatewayDevice.X_ALU_OntOpticalParam',
                'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1.MACAddress',
                'Device.WANDevice',
                'Device.Optical',
                'Device.PPP',
                'Device.IP',
                'Device.Ethernet.Interface.1.MACAddress',
            ];

            $query = [
                'projection' => implode(',', $projectionFields)
            ];

            $adminAcs = app(\App\Livewire\ACS\Device\Index::class);
            $syncStartTime = now()->subMinutes(1);
            $count = 0;
            $batchSize = 250;
            $skip = 0;
            $maxLimit = 10000;

            while ($skip < $maxLimit) {
                $devices = $driver->listDevices($query, $batchSize, $skip);
                if (empty($devices) || !is_array($devices)) {
                    break;
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($devices, $adminAcs, &$count) {
                    foreach ($devices as $deviceData) {
                        $savedId = $adminAcs->saveDeviceData($deviceData, false);
                        if ($savedId) {
                            $count++;
                        }
                    }
                });

                $fetched = count($devices);
                unset($devices);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }

                if ($fetched < $batchSize) {
                    break;
                }
                $skip += $fetched;
            }

            if ($count > 0) {
                ACSDevice::forReseller()->where(function ($q) use ($syncStartTime) {
                    $q->whereNull('last_contact')
                      ->orWhere('last_contact', '<', $syncStartTime);
                })->where('status', '!=', 'offline')
                  ->update(['status' => 'offline']);
            }

            $msg = "Sinkronisasi selesai: Berhasil memperbarui {$count} perangkat dari GenieACS!";
            session()->flash('success', $msg);
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal sinkronisasi GenieACS: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Gagal sync GenieACS: ' . $e->getMessage());
        }
    }

    public function summon(int $deviceId): void
    {
        $device = ACSDevice::forReseller()->findOrFail($deviceId);
        $targetId = $device->uuid ?: $device->serial_number;

        try {
            $driver = app(GenieACSDriver::class);
            $success = $driver->summonDevice((string) $targetId);
            if ($success) {
                session()->flash('success', "Perintah Summon (Refresh Data) berhasil dikirim ke perangkat ({$device->serial_number}).");
            } else {
                session()->flash('error', 'Gagal melakukan summon. Perangkat mungkin sedang offline atau IP tidak terjangkau.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal summon perangkat: ' . $e->getMessage());
        }
    }

    public function rebootDevice(int $deviceId): void
    {
        $user = auth()->user();
        if ($user && !$user->hasPermission('onu.reboot') && !$user->hasPermission('olt.view') && !$user->hasPermission('onu.view')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk melakukan reboot perangkat.');
            return;
        }

        $device = ACSDevice::forReseller()->findOrFail($deviceId);

        try {
            $genie = app(GenieACSDriver::class);
            $targetId = $device->uuid ?: $device->serial_number;
            $ok = $genie->rebootDevice((string) $targetId);
            if ($ok) {
                session()->flash('success', "Perintah Reboot untuk modem {$device->serial_number} berhasil dikirim via TR-069!");
            } else {
                session()->flash('error', "GenieACS menolak atau menunda perintah reboot untuk modem {$device->serial_number}.");
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengirim perintah reboot: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = ACSDevice::forReseller()
            ->with([
                'vendor',
                'olt',
                'odp',
                'customerService' => function ($csQ) {
                    $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                        ->with([
                            'customer' => fn ($cq) => $cq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation']),
                        ]);
                },
                'onu' => function ($oq) {
                    $oq->withoutGlobalScopes(['branch_isolation'])
                        ->with([
                            'olt',
                            'customerService' => function ($csQ) {
                                $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                    ->with([
                                        'customer' => fn ($cq) => $cq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation']),
                                    ]);
                            },
                        ]);
                },
            ])
            ->when(trim($this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('serial_number', 'like', $like)
                        ->orWhere('mac_address', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('connection_request_url', 'like', $like)
                        ->orWhere('model', 'like', $like)
                        ->orWhere('manufacturer', 'like', $like)
                        ->orWhereHas('customerService', function ($csQ) use ($like) {
                            $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                ->whereHas('customer', function ($c) use ($like) {
                                    $c->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                      ->where(function ($sq) use ($like) {
                                          $sq->where('name', 'like', $like)
                                             ->orWhere('code', 'like', $like)
                                             ->orWhere('phone', 'like', $like);
                                      });
                                });
                        });
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status));

        $devices = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.reseller-portal.network.acs-device.index', [
            'devices'   => $devices,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}
