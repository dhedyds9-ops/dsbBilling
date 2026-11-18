<?php

namespace App\Livewire\ResellerPortal\Network\Map;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\JointClosure;
use App\Models\CRM\Customer;
use App\Livewire\Gis\GisMap;
use App\Services\ISP\NodePhotoService;
use Illuminate\Support\Facades\Cache;
use Livewire\WithFileUploads;

class Index extends AdminComponent
{
    use WithFileUploads;

    // Modal state for Add / Edit node
    public bool $showModal = false;
    public string $modalMode = 'create'; // 'create' | 'edit'
    public string $nodeType = 'odp';    // 'odp' | 'odc'
    public ?int $nodeId = null;

    // Form fields
    public string $nodeName = '';
    public string $nodeCode = '';
    public ?int $parentId = null; // odc_id for odp, or olt_id for odc
    public int $portCount = 8;
    public string $latitude = '';
    public string $longitude = '';
    public string $address = '';
    public string $status = 'active';
    public $photo = null;
    public array $existingPhotos = [];
    public ?string $feedbackMessage = null;

    protected $listeners = [
        'open-create-modal' => 'openCreateModal',
        'open-edit-modal' => 'openEditModal',
        'update-coordinates' => 'updateCoordinates',
    ];

    public function mount()
    {
        parent::mount();
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => '#'],
            ['label' => 'Peta Satelit', 'url' => '#'],
        ];
    }

    public function openCreateModal(string $type = 'odp', ?float $lat = null, ?float $lng = null): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->modalMode = 'create';
        $this->nodeType = in_array($type, ['odp', 'odc']) ? $type : 'odp';
        $this->nodeId = null;
        $this->nodeName = '';
        $this->parentId = null;
        $this->latitude = $lat ? (string)$lat : '';
        $this->longitude = $lng ? (string)$lng : '';
        $this->address = '';
        $this->status = 'active';
        $this->photo = null;
        $this->existingPhotos = [];
        $this->feedbackMessage = null;

        if ($this->nodeType === 'odc') {
            $count = Odc::withoutGlobalScopes()->count() + 1;
            $this->nodeCode = 'ODC-' . sprintf('%03d', $count);
            $this->portCount = 48;
        } else {
            $count = Odp::withoutGlobalScopes()->count() + 1;
            $this->nodeCode = 'ODP-' . sprintf('%03d', $count);
            $this->portCount = 8;
        }

        $this->showModal = true;
    }

    public function openEditModal(string $type, int $id): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->modalMode = 'edit';
        $this->nodeType = in_array($type, ['odp', 'odc']) ? $type : 'odp';
        $this->nodeId = $id;
        $this->photo = null;
        $this->feedbackMessage = null;

        if ($this->nodeType === 'odc') {
            $odc = Odc::withoutGlobalScopes()->find($id);
            if (!$odc) {
                $this->dispatch('node-saved', ['message' => 'ODC tidak ditemukan!', 'type' => 'error']);
                return;
            }
            $this->nodeName = (string)$odc->name;
            $this->nodeCode = (string)$odc->code;
            $this->parentId = $odc->olt_id;
            $this->portCount = (int)($odc->port_count ?: 48);
            $this->latitude = (string)$odc->latitude;
            $this->longitude = (string)$odc->longitude;
            $this->address = (string)($odc->address ?? '');
            $this->status = $odc->status ?? 'active';
            $this->existingPhotos = (array)($odc->photos ?? []);
        } else {
            $odp = Odp::withoutGlobalScopes()->find($id);
            if (!$odp) {
                $this->dispatch('node-saved', ['message' => 'ODP tidak ditemukan!', 'type' => 'error']);
                return;
            }
            $this->nodeName = (string)$odp->name;
            $this->nodeCode = (string)$odp->code;
            $this->parentId = $odp->odc_id;
            $this->portCount = (int)($odp->port_count ?: 8);
            $this->latitude = (string)$odp->latitude;
            $this->longitude = (string)$odp->longitude;
            $this->address = (string)($odp->address ?: ($odp->description ?? ''));
            $this->status = $odp->status ?? 'active';
            $this->existingPhotos = (array)($odp->photos ?? []);
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->showModal = false;
        $this->photo = null;
    }

    public function deleteExistingPhoto(int $index): void
    {
        if (!$this->nodeId) return;

        $model = $this->nodeType === 'odc'
            ? Odc::withoutGlobalScopes()->find($this->nodeId)
            : Odp::withoutGlobalScopes()->find($this->nodeId);

        if ($model && !empty($model->photos)) {
            $photos = $model->photos;
            if (isset($photos[$index])) {
                unset($photos[$index]);
                $model->photos = array_values($photos);
                $model->save();
                $this->existingPhotos = $model->photos ?? [];
            }
        }
    }

    public function save(): void
    {
        $table = $this->nodeType === 'odc' ? 'odcs' : 'odps';

        $this->validate([
            'nodeName' => 'required|string|max:255',
            'nodeCode' => 'required|string|max:100|unique:' . $table . ',code,' . ($this->nodeId ?: 'NULL') . ',id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'portCount' => 'required|integer|min:1|max:512',
            'parentId' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|max:10240',
        ]);

        $user = auth()->user();
        $effectiveResellerId = $user ? $user->getEffectiveResellerId() : null;
        $branchId = $user?->branch_id;

        $targetModel = null;

        if ($this->nodeType === 'odc') {
            if ($this->modalMode === 'edit' && $this->nodeId) {
                $odc = Odc::withoutGlobalScopes()->findOrFail($this->nodeId);
                $odc->update([
                    'name' => $this->nodeName,
                    'code' => $this->nodeCode,
                    'olt_id' => $this->parentId,
                    'port_count' => $this->portCount,
                    'latitude' => (string)$this->latitude,
                    'longitude' => (string)$this->longitude,
                    'address' => $this->address,
                    'status' => $this->status,
                    'updated_by' => $user?->id,
                ]);
                $targetModel = $odc;
            } else {
                $odc = Odc::create([
                    'name' => $this->nodeName,
                    'code' => $this->nodeCode,
                    'olt_id' => $this->parentId,
                    'port_count' => $this->portCount,
                    'latitude' => (string)$this->latitude,
                    'longitude' => (string)$this->longitude,
                    'address' => $this->address,
                    'status' => $this->status,
                    'branch_id' => $branchId,
                    'reseller_id' => $effectiveResellerId,
                    'created_by' => $user?->id,
                ]);
                $targetModel = $odc;
            }
        } else {
            if ($this->modalMode === 'edit' && $this->nodeId) {
                $odp = Odp::withoutGlobalScopes()->findOrFail($this->nodeId);
                $odp->update([
                    'name' => $this->nodeName,
                    'code' => $this->nodeCode,
                    'odc_id' => $this->parentId,
                    'port_count' => $this->portCount,
                    'latitude' => (string)$this->latitude,
                    'longitude' => (string)$this->longitude,
                    'address' => $this->address,
                    'status' => $this->status,
                    'updated_by' => $user?->id,
                ]);
                $targetModel = $odp;
            } else {
                $odp = Odp::create([
                    'name' => $this->nodeName,
                    'code' => $this->nodeCode,
                    'odc_id' => $this->parentId,
                    'port_count' => $this->portCount,
                    'latitude' => (string)$this->latitude,
                    'longitude' => (string)$this->longitude,
                    'address' => $this->address,
                    'status' => $this->status,
                    'branch_id' => $branchId,
                    'reseller_id' => $effectiveResellerId,
                    'created_by' => $user?->id,
                ]);
                $targetModel = $odp;
            }
        }

        if ($this->photo && $targetModel) {
            NodePhotoService::attachPhoto($targetModel, $this->photo, $this->nodeType, true);
        }

        GisMap::flushCache();
        Cache::forget('gis_map_data');
        Cache::forget('gis_map_nodes');

        $actionWord = $this->modalMode === 'edit' ? 'diperbarui' : 'didaftarkan';
        $msg = 'Titik ' . strtoupper($this->nodeType) . " berhasil {$actionWord}!";

        $this->closeModal();
        $this->dispatch('node-saved', ['message' => $msg]);
    }

    public function updateCoordinates(string $type, int $id, float $lat, float $lng): void
    {
        $latStr = (string)$lat;
        $lngStr = (string)$lng;

        if ($type === 'customer') {
            $customer = Customer::withoutGlobalScopes()->find($id);
            if ($customer) {
                $customer->latitude = $latStr;
                $customer->longitude = $lngStr;
                $customer->save();
            }
        } elseif ($type === 'odp') {
            $odp = Odp::withoutGlobalScopes()->find($id);
            if ($odp) {
                $odp->latitude = $latStr;
                $odp->longitude = $lngStr;
                $odp->save();
            }
        } elseif ($type === 'odc') {
            $odc = Odc::withoutGlobalScopes()->find($id);
            if ($odc) {
                $odc->latitude = $latStr;
                $odc->longitude = $lngStr;
                $odc->save();
            }
        }

        GisMap::flushCache();
        Cache::forget('gis_map_data');
        Cache::forget('gis_map_nodes');

        $this->dispatch('node-saved', [
            'message' => 'Titik koordinat berhasil diperbarui!',
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $ownerId = $user?->hasRole('reseller') ? $user->getEffectiveResellerId() : null;

        $customers = Customer::withoutGlobalScopes()->with([
            'customerServices' => function($q) {
                $q->withoutGlobalScopes()->with([
                    'onu' => fn($oq) => $oq->withoutGlobalScopes(),
                    'pppoeUser' => fn($pq) => $pq->withoutGlobalScopes(),
                    'hotspotUser' => fn($hq) => $hq->withoutGlobalScopes(),
                ]);
            }
        ])
            ->where(function($q) use ($ownerId) {
                if ($ownerId) {
                    $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                }
            })
            ->get()
            ->filter(function ($c) {
                $hasCoords = !empty($c->latitude) && !empty($c->longitude) && is_numeric($c->latitude) && is_numeric($c->longitude);
                if ($hasCoords) return true;
                $service = $c->customerServices->first();
                $onu = $service?->onu;
                return !empty($onu?->latitude) && !empty($onu?->longitude) && is_numeric($onu->latitude) && is_numeric($onu->longitude);
            })
            ->map(function ($c) {
                $service = $c->customerServices->firstWhere('status', 'active') ?? $c->customerServices->first();
                $onu = $service?->onu;
                $pppoe = $service?->pppoeUser;
                $hotspot = $service?->hotspotUser;

                if (empty($c->latitude) || empty($c->longitude) || !is_numeric($c->latitude) || !is_numeric($c->longitude)) {
                    if ($onu && !empty($onu->latitude) && !empty($onu->longitude)) {
                        $c->latitude = $onu->latitude;
                        $c->longitude = $onu->longitude;
                    }
                }

                $isOnline = false;
                if ($pppoe && in_array(strtolower($pppoe->status ?? ''), ['active', 'online'])) {
                    $isOnline = true;
                } elseif ($hotspot && in_array(strtolower($hotspot->status ?? ''), ['active', 'online'])) {
                    $isOnline = true;
                } elseif ($service && strtolower($service->status ?? '') === 'active') {
                    $isOnline = true;
                } elseif ($onu && $onu->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 10) {
                    $isOnline = true;
                }

                $c->is_online = $isOnline;
                $c->odp_id = $onu?->odp_id ?? $pppoe?->odp_id ?? $c->odp_id;
                $c->ssid_name = $onu?->wifi_ssid ?: 'WIFI-' . $c->name;
                $c->service_type = $pppoe ? 'PPPoE' : ($hotspot ? 'Hotspot' : 'Internet');

                return $c;
            })
            ->values();

        $formatPhotos = function ($rawPhotos) {
            return array_values(array_filter(array_map(function ($p) {
                if (empty($p)) return null;
                $clean = preg_replace('/^https?:\/\/[^\/]+(\/storage\/.*)$/', '$1', (string) $p);
                if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, '/storage/')) return $clean;
                return '/storage/' . ltrim($clean, '/');
            }, (array) ($rawPhotos ?? []))));
        };

        $olts = Olt::withoutGlobalScopes()->forReseller()->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'ip_address' => $o->ip_address ?: ($o->host ?: '-'),
            'model' => $o->model ?: '-',
            'pon_port_count' => (int)($o->pon_port_count ?: ($o->port_count ?: 8)),
            'address' => $o->address ?: '-',
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => $formatPhotos($o->photos),
        ]);

        $odcs = Odc::withoutGlobalScopes()->forReseller()->with(['olt:id,name,latitude,longitude'])->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'capacity' => (int)($o->port_count ?: ($o->capacity ?: 48)),
            'olt_id' => $o->olt_id,
            'olt_name' => $o->olt?->name ?? 'OLT Default',
            'pon_port' => $o->pon_port ?? '-',
            'address' => $o->address ?: '-',
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => $formatPhotos($o->photos),
        ]);

        $closures = JointClosure::withoutGlobalScopes()->forReseller()->with(['odc:id,name,latitude,longitude'])->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'type' => $c->type ?? 'Dome',
            'port_count' => (int)($c->port_count ?: 24),
            'odc_id' => $c->odc_id,
            'odc_name' => $c->odc?->name ?? 'N/A',
            'address' => $c->address ?: '-',
            'latitude' => $c->latitude ? (float)$c->latitude : null,
            'longitude' => $c->longitude ? (float)$c->longitude : null,
            'photos' => $formatPhotos($c->photos),
        ]);

        $odps = Odp::withoutGlobalScopes()->forReseller()->with(['olt:id,name', 'odc:id,name,latitude,longitude'])->get()->map(function($o) use ($formatPhotos) {
            $totalP = (int)($o->port_count ?: 8);
            $usedP = (int)($o->used_port_count ?? 0);
            $reservedP = (int)($o->reserved_port_count ?? 0);
            $availP = max(0, $totalP - $usedP - $reservedP);
            return [
                'id' => $o->id,
                'name' => $o->name,
                'code' => $o->code,
                'port_count' => $totalP,
                'used_port_count' => $usedP,
                'available_port_count' => $availP,
                'odc_id' => $o->odc_id,
                'odc_name' => $o->odc?->name ?? 'N/A',
                'address' => $o->address ?: ($o->description ?: '-'),
                'latitude' => $o->latitude ? (float)$o->latitude : null,
                'longitude' => $o->longitude ? (float)$o->longitude : null,
                'photos' => $formatPhotos($o->photos),
            ];
        });

        $oltOptions = Olt::withoutGlobalScopes()->forReseller()->orderBy('name')->get(['id', 'name', 'code']);
        $odcOptions = Odc::withoutGlobalScopes()->forReseller()->orderBy('name')->get(['id', 'name', 'code']);

        return view('livewire.reseller-portal.network.map.index', [
            'showModal' => $this->showModal,
            'modalMode' => $this->modalMode,
            'nodeType' => $this->nodeType,
            'nodeId' => $this->nodeId,
            'nodeName' => $this->nodeName,
            'nodeCode' => $this->nodeCode,
            'parentId' => $this->parentId,
            'portCount' => $this->portCount,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'status' => $this->status,
            'photo' => $this->photo,
            'existingPhotos' => $this->existingPhotos,
            'customers' => $customers,
            'odps' => $odps,
            'closures' => $closures,
            'odcs' => $odcs,
            'olts' => $olts,
            'oltOptions' => $oltOptions,
            'odcOptions' => $odcOptions,
        ]);
    }
}
