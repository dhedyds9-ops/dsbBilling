<?php

namespace App\Livewire\Isp\Technician\Odp;

use App\Models\ISP\Odp;
use App\Models\ISP\Odc;
use App\Models\ISP\Olt;
use App\Models\ISP\JointClosure;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Nearest extends Component
{
    use WithFileUploads;

    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 5; // Default 5 km
    public string $search = '';
    public string $statusFilter = 'all'; // all, available, full
    public array $odps = [];
    public array $allNodes = [
        'olts' => [],
        'odcs' => [],
        'closures' => [],
    ];
    public ?int $selectedOdpId = null;

    // Create New Node Modal
    public bool $showCreateModal = false;
    public string $newNodeType = 'odp'; // odp, closure
    public string $newNodeCode = '';
    public string $newNodeName = '';
    public ?float $newNodeLat = null;
    public ?float $newNodeLng = null;
    public string $newNodeAddress = '';
    public int $newNodePortCount = 8;
    public string $newNodeClosureType = 'Dome';
    public $newNodePhoto = null;
    public ?string $createSuccessMessage = null;

    // Upload Additional Photo Modal (untuk titik yang sudah ada)
    public bool $showUploadPhotoModal = false;
    public string $uploadTargetType = 'odp';
    public ?int $uploadTargetId = null;
    public string $uploadTargetName = '';
    public $additionalPhoto = null;
    public $croppedPhotoData = null;
    public $existingTargetPhoto = null;

    public function mount()
    {
        $this->loadNearestOdps();
        $this->loadAllTopology();
    }

    public function loadAllTopology()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        $oltsQuery = Olt::withoutGlobalScope('branch_isolation')->where('status', '!=', 'inactive');
        if ($branchId && !$user->isSuperAdmin()) {
            $oltsQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $formatPhotos = function ($rawPhotos) {
            return array_values(array_filter(array_map(function ($p) {
                if (empty($p)) return null;
                if (str_starts_with($p, '/storage/data:image')) return substr($p, 9);
                if (str_starts_with($p, 'storage/data:image')) return substr($p, 8);
                if (str_starts_with($p, 'data:image')) return $p;
                $clean = preg_replace('/^https?:\/\/[^\/]+(\/storage\/.*)$/', '$1', (string) $p);
                if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, '/storage/')) return $clean;
                return '/storage/' . ltrim($clean, '/');
            }, (array) ($rawPhotos ?? []))));
        };

        $olts = $oltsQuery->get()->map(fn($o) => [
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
        ])->toArray();

        $odcsQuery = Odc::withoutGlobalScopes()->with(['olt:id,name,latitude,longitude']);
        if ($branchId && !$user->isSuperAdmin()) {
            $odcsQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $odcs = $odcsQuery->get()->map(function($o) use ($formatPhotos) {
            $oltDist = null;
            if ($o->olt && $o->olt->latitude && $o->olt->longitude && $o->latitude && $o->longitude) {
                $oltDist = round($this->calculateHaversine((float)$o->latitude, (float)$o->longitude, (float)$o->olt->latitude, (float)$o->olt->longitude), 2);
            }
            return [
                'id' => $o->id,
                'name' => $o->name,
                'code' => $o->code,
                'capacity' => (int)($o->port_count ?: ($o->capacity ?: 48)),
                'olt_id' => $o->olt_id,
                'olt_name' => $o->olt?->name ?? 'OLT Default',
                'olt_distance' => $oltDist,
                'pon_port' => $o->pon_port ?? '-',
                'address' => $o->address ?: ($o->description ?: '-'),
                'latitude' => $o->latitude ? (float)$o->latitude : null,
                'longitude' => $o->longitude ? (float)$o->longitude : null,
                'photos' => $formatPhotos($o->photos),
            ];
        })->toArray();

        $closuresQuery = JointClosure::withoutGlobalScopes()->with(['odc:id,name,latitude,longitude']);
        if ($branchId && !$user->isSuperAdmin()) {
            $closuresQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $closures = $closuresQuery->get()->map(function($c) use ($formatPhotos) {
            $odcDist = null;
            if ($c->odc && $c->odc->latitude && $c->odc->longitude && $c->latitude && $c->longitude) {
                $odcDist = round($this->calculateHaversine((float)$c->latitude, (float)$c->longitude, (float)$c->odc->latitude, (float)$c->odc->longitude), 2);
            }
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'type' => $c->type ?? 'Dome',
                'port_count' => (int)($c->port_count ?: 24),
                'odc_id' => $c->odc_id,
                'odc_name' => $c->odc?->name ?? 'N/A',
                'odc_distance' => $odcDist,
                'address' => $c->address ?: ($c->description ?: '-'),
                'latitude' => $c->latitude ? (float)$c->latitude : null,
                'longitude' => $c->longitude ? (float)$c->longitude : null,
                'photos' => $formatPhotos($c->photos),
            ];
        })->toArray();

        $this->allNodes = [
            'olts' => $olts,
            'odcs' => $odcs,
            'closures' => $closures,
        ];
    }

    public function openCreateModal($lat = null, $lng = null)
    {
        $this->newNodeLat = $lat ? (float)$lat : ($this->latitude ?? -6.2088);
        $this->newNodeLng = $lng ? (float)$lng : ($this->longitude ?? 106.8456);
        $this->newNodeCode = ($this->newNodeType === 'closure' ? 'JC-' : 'ODP-') . strtoupper(substr(uniqid(), -5));
        $this->newNodeName = ($this->newNodeType === 'closure' ? 'Closure ' : 'ODP ') . substr($this->newNodeCode, -5);
        $this->newNodeAddress = '';
        $this->newNodePhoto = null;
        $this->createSuccessMessage = null;
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->newNodePhoto = null;
    }

    public function updatedNewNodeType()
    {
        $prefix = $this->newNodeType === 'closure' ? 'JC-' : 'ODP-';
        $label = $this->newNodeType === 'closure' ? 'Closure ' : 'ODP ';
        $suffix = strtoupper(substr(uniqid(), -5));
        $this->newNodeCode = $prefix . $suffix;
        $this->newNodeName = $label . $suffix;
        $this->newNodePortCount = $this->newNodeType === 'closure' ? 24 : 8;
    }

    public function saveNewNode()
    {
        $this->validate([
            'newNodeType' => 'required|in:odp,closure',
            'newNodeCode' => 'required|string|max:100',
            'newNodeName' => 'required|string|max:255',
            'newNodeLat' => 'required|numeric',
            'newNodeLng' => 'required|numeric',
            'newNodeAddress' => 'nullable|string|max:500',
            'newNodePortCount' => 'required|integer|min:1|max:512',
            'newNodePhoto' => 'nullable|image|max:10240',
        ]);

        $user = auth()->user();
        $photos = [];

        if ($this->newNodePhoto) {
            $folder = $this->newNodeType === 'closure' ? 'closures' : 'odp';
            $dataUri = \App\Services\ISP\NodePhotoService::createDataUri($this->newNodePhoto);
            $relUrl = \App\Services\ISP\NodePhotoService::mirrorPhysicalFile($this->newNodePhoto, $folder);
            if ($dataUri) $photos[] = $dataUri;
            if ($relUrl) $photos[] = $relUrl;
        }

        if ($this->newNodeType === 'odp') {
            Odp::create([
                'code' => $this->newNodeCode,
                'name' => $this->newNodeName,
                'latitude' => (string)$this->newNodeLat,
                'longitude' => (string)$this->newNodeLng,
                'address' => $this->newNodeAddress,
                'port_count' => $this->newNodePortCount,
                'branch_id' => $user?->branch_id,
                'photos' => $photos,
                'created_by' => $user?->id,
            ]);
        } else {
            JointClosure::create([
                'code' => $this->newNodeCode,
                'name' => $this->newNodeName,
                'type' => $this->newNodeClosureType,
                'latitude' => (string)$this->newNodeLat,
                'longitude' => (string)$this->newNodeLng,
                'address' => $this->newNodeAddress,
                'port_count' => $this->newNodePortCount,
                'branch_id' => $user?->branch_id,
                'photos' => $photos,
                'created_by' => $user?->id,
            ]);
        }

        $this->createSuccessMessage = "Titik " . strtoupper($this->newNodeType) . " {$this->newNodeName} berhasil disimpan!";
        $this->loadNearestOdps();
        $this->loadAllTopology();
        $this->showCreateModal = false;
        $this->newNodePhoto = null;
    }

    public function openUploadPhotoModal(string $type, int $id, string $name)
    {
        $this->uploadTargetType = $type;
        $this->uploadTargetId = $id;
        $this->uploadTargetName = $name;
        $this->additionalPhoto = null;
        $this->croppedPhotoData = null;

        $node = match ($type) {
            'olt' => Olt::withoutGlobalScopes()->find($id),
            'odc' => Odc::withoutGlobalScopes()->find($id),
            'closure' => JointClosure::withoutGlobalScopes()->find($id),
            default => Odp::withoutGlobalScopes()->find($id),
        };
        $this->existingTargetPhoto = ($node && is_array($node->photos) && count($node->photos) > 0) ? $node->photos[0] : null;
        $this->showUploadPhotoModal = true;
    }

    public function closeUploadPhotoModal()
    {
        $this->showUploadPhotoModal = false;
        $this->additionalPhoto = null;
        $this->croppedPhotoData = null;
        $this->existingTargetPhoto = null;
    }

    public function saveAdditionalPhoto()
    {
        if (!$this->croppedPhotoData) {
            $this->validate([
                'additionalPhoto' => 'required|image|max:12288',
            ]);
        }

        $node = match ($this->uploadTargetType) {
            'olt' => Olt::withoutGlobalScopes()->find($this->uploadTargetId),
            'odc' => Odc::withoutGlobalScopes()->find($this->uploadTargetId),
            'closure' => JointClosure::withoutGlobalScopes()->find($this->uploadTargetId),
            default => Odp::withoutGlobalScopes()->find($this->uploadTargetId),
        };

        if ($node) {
            $target = $this->croppedPhotoData ?: $this->additionalPhoto;
            \App\Services\ISP\NodePhotoService::attachPhoto($node, $target, $this->uploadTargetType, true);
        }

        $this->createSuccessMessage = "Foto tiang untuk {$this->uploadTargetName} berhasil disimpan!";
        $this->showUploadPhotoModal = false;
        $this->additionalPhoto = null;
        $this->croppedPhotoData = null;
        $this->existingTargetPhoto = null;
        $this->loadNearestOdps();
        $this->loadAllTopology();
        $this->js('window.location.reload()');
    }

    public function deleteCurrentPhoto()
    {
        $node = match ($this->uploadTargetType) {
            'olt' => Olt::withoutGlobalScopes()->find($this->uploadTargetId),
            'odc' => Odc::withoutGlobalScopes()->find($this->uploadTargetId),
            'closure' => JointClosure::withoutGlobalScopes()->find($this->uploadTargetId),
            default => Odp::withoutGlobalScopes()->find($this->uploadTargetId),
        };

        if ($node) {
            \App\Services\ISP\NodePhotoService::deletePhotos($node);
        }

        $this->createSuccessMessage = "Foto dokumentasi tiang untuk {$this->uploadTargetName} berhasil dihapus!";
        $this->showUploadPhotoModal = false;
        $this->additionalPhoto = null;
        $this->croppedPhotoData = null;
        $this->existingTargetPhoto = null;
        $this->loadNearestOdps();
        $this->loadAllTopology();
        $this->js('window.location.reload()');
    }

    public function updatedSearch()
    {
        $this->loadNearestOdps();
    }

    public function updatedRadius()
    {
        $this->loadNearestOdps();
    }

    public function updatedStatusFilter()
    {
        $this->loadNearestOdps();
    }

    public function setLocation($lat, $lng)
    {
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;
        $this->loadNearestOdps();
    }

    public function selectOdp(int $id)
    {
        $this->selectedOdpId = $id;
        $target = collect($this->odps)->firstWhere('id', $id);
        if ($target && !empty($target['latitude']) && !empty($target['longitude'])) {
            $this->dispatch('focus-odp', [
                'lat' => (float) $target['latitude'],
                'lng' => (float) $target['longitude'],
                'id' => $id,
                'code' => $target['code'],
            ]);
        }
    }

    public function loadNearestOdps()
    {
        $user = auth()->user();

        $query = Odp::withoutGlobalScopes()->with(['olt:id,name', 'odc:id,name,latitude,longitude', 'branch:id,name'])
            ->when($user && $user->branch_id && !$user->isSuperAdmin(), function ($q) use ($user) {
                $q->where(function ($b) use ($user) {
                    $b->where('branch_id', $user->branch_id)
                      ->orWhereNull('branch_id')
                      ->orWhere('branch_id', 1);
                });
            })
            ->where('status', '!=', 'inactive');

        // Pencarian Teks ODP (Kode, Nama, atau Alamat)
        if (!empty(trim($this->search))) {
            $keyword = trim($this->search);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Filter Port
        if ($this->statusFilter === 'available') {
            $query->whereRaw('(COALESCE(port_count, 8) - COALESCE(used_port_count, 0) - COALESCE(reserved_port_count, 0)) > 0');
        } elseif ($this->statusFilter === 'full') {
            $query->whereRaw('(COALESCE(port_count, 8) - COALESCE(used_port_count, 0) - COALESCE(reserved_port_count, 0)) <= 0');
        }

        $allOdps = $query->get();

        $hasGps = ($this->latitude !== null && $this->longitude !== null);
        $userLat = $this->latitude;
        $userLng = $this->longitude;

        $results = $allOdps->map(function ($odp) use ($hasGps, $userLat, $userLng) {
            $dist = null;
            if ($hasGps && $odp->latitude && $odp->longitude) {
                $dist = $this->calculateHaversine((float) $userLat, (float) $userLng, (float) $odp->latitude, (float) $odp->longitude);
            }

            $totalP = (int) ($odp->port_count ?: 8);
            $usedP = (int) ($odp->used_port_count ?? 0);
            $reservedP = (int) ($odp->reserved_port_count ?? 0);
            $availP = max(0, $totalP - $usedP - $reservedP);
            $occupancy = $totalP > 0 ? round(($usedP / $totalP) * 100, 1) : 0;

            $photos = array_values(array_filter(array_map(function($p) {
                if (empty($p)) return null;
                $clean = preg_replace('/^https?:\/\/[^\/]+(\/storage\/.*)$/', '$1', (string) $p);
                if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, '/storage/')) return $clean;
                return '/storage/' . ltrim($clean, '/');
            }, (array) ($odp->photos ?? []))));

            $odcDist = null;
            if ($odp->odc && $odp->odc->latitude && $odp->odc->longitude && $odp->latitude && $odp->longitude) {
                $odcDist = round($this->calculateHaversine((float)$odp->latitude, (float)$odp->longitude, (float)$odp->odc->latitude, (float)$odp->odc->longitude), 2);
            }

            return [
                'id' => $odp->id,
                'code' => $odp->code,
                'name' => $odp->name,
                'latitude' => $odp->latitude ? (float) $odp->latitude : null,
                'longitude' => $odp->longitude ? (float) $odp->longitude : null,
                'distance' => $dist !== null ? round($dist, 2) : null,
                'port_count' => $totalP,
                'used_port_count' => $usedP,
                'reserved_port_count' => $reservedP,
                'available_port_count' => $availP,
                'occupancy' => $occupancy,
                'address' => $odp->address ?: ($odp->description ?: 'Alamat belum diisi'),
                'status' => $odp->status,
                'olt_name' => $odp->olt?->name ?? 'OLT Default',
                'odc_id' => $odp->odc_id,
                'odc_name' => $odp->odc?->name ?? 'N/A',
                'odc_distance' => $odcDist,
                'color' => $odp->color ?? 'blue',
                'cable_no' => $odp->cable_no ?? '-',
                'photos' => $photos,
            ];
        });

        // Filter radius hanya jika tidak sedang search teks bebas dan ada GPS & radius > 0
        if (empty(trim($this->search)) && $hasGps && $this->radius > 0) {
            $results = $results->filter(function ($item) {
                return $item['distance'] !== null && $item['distance'] <= $this->radius;
            });
        }

        // Urutkan jarak terdekat jika ada GPS, atau urutkan abjad jika tidak ada
        if ($hasGps) {
            $results = $results->sortBy(function ($item) {
                return $item['distance'] ?? 999999;
            });
        } else {
            $results = $results->sortBy('name');
        }

        $this->odps = $results->values()->toArray();

        // Tentukan titik pusat peta default jika belum ada GPS
        if (!$hasGps && count($this->odps) > 0) {
            foreach ($this->odps as $item) {
                if ($item['latitude'] && $item['longitude']) {
                    $this->latitude = $item['latitude'];
                    $this->longitude = $item['longitude'];
                    break;
                }
            }
        }

        $this->dispatch('odps-updated', [
            'odps' => $this->odps,
            'center' => [
                'lat' => $this->latitude ?? -6.200000,
                'lng' => $this->longitude ?? 106.816666,
            ],
            'hasGps' => $hasGps,
        ]);
    }

    private function calculateHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function render()
    {
        return view('livewire.isp.technician.odp.nearest');
    }
}
