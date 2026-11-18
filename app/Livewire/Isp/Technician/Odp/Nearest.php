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

    public function mount()
    {
        $this->loadNearestOdps();
        $this->loadAllTopology();
    }

    public function loadAllTopology()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        $oltsQuery = Olt::withoutGlobalScopes();
        if ($branchId && !$user->isSuperAdmin()) {
            $oltsQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $olts = $oltsQuery->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => $o->photos ?? [],
        ])->toArray();

        $odcsQuery = Odc::withoutGlobalScopes();
        if ($branchId && !$user->isSuperAdmin()) {
            $odcsQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $odcs = $odcsQuery->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
            'latitude' => $o->latitude ? (float)$o->latitude : null,
            'longitude' => $o->longitude ? (float)$o->longitude : null,
            'photos' => $o->photos ?? [],
        ])->toArray();

        $closuresQuery = JointClosure::withoutGlobalScopes();
        if ($branchId && !$user->isSuperAdmin()) {
            $closuresQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('branch_id', 1);
            });
        }
        $closures = $closuresQuery->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'type' => $c->type ?? 'Dome',
            'latitude' => $c->latitude ? (float)$c->latitude : null,
            'longitude' => $c->longitude ? (float)$c->longitude : null,
            'photos' => $c->photos ?? [],
        ])->toArray();

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
            $path = $this->newNodePhoto->store("network_photos/{$folder}", 'public');
            if ($path) {
                $photos[] = Storage::url($path);
            }
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
        $this->showUploadPhotoModal = true;
    }

    public function closeUploadPhotoModal()
    {
        $this->showUploadPhotoModal = false;
        $this->additionalPhoto = null;
    }

    public function saveAdditionalPhoto()
    {
        $this->validate([
            'additionalPhoto' => 'required|image|max:10240',
        ]);

        $folder = ($this->uploadTargetType === 'closure') ? 'closures' : 'odp';
        $path = $this->additionalPhoto->store("network_photos/{$folder}", 'public');

        if ($path) {
            $photoUrl = Storage::url($path);
            $node = ($this->uploadTargetType === 'closure')
                ? JointClosure::withoutGlobalScopes()->find($this->uploadTargetId)
                : Odp::withoutGlobalScopes()->find($this->uploadTargetId);

            if ($node) {
                $current = is_array($node->photos) ? $node->photos : [];
                $current[] = $photoUrl;
                $node->photos = array_values(array_unique($current));
                $node->save();
            }
        }

        $this->createSuccessMessage = "Foto tiang untuk {$this->uploadTargetName} berhasil diunggah!";
        $this->showUploadPhotoModal = false;
        $this->additionalPhoto = null;
        $this->loadNearestOdps();
        $this->loadAllTopology();
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

        $query = Odp::withoutGlobalScopes()->with(['olt', 'branch'])
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
            $query->whereRaw('(port_count - used_port_count - reserved_port_count) > 0');
        } elseif ($this->statusFilter === 'full') {
            $query->whereRaw('(port_count - used_port_count - reserved_port_count) <= 0');
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

            return [
                'id' => $odp->id,
                'code' => $odp->code,
                'name' => $odp->name,
                'latitude' => $odp->latitude ? (float) $odp->latitude : null,
                'longitude' => $odp->longitude ? (float) $odp->longitude : null,
                'distance' => $dist !== null ? round($dist, 2) : null,
                'port_count' => (int) $odp->port_count,
                'used_port_count' => (int) $odp->used_port_count,
                'available_port_count' => (int) $odp->availablePortCount,
                'occupancy' => (float) $odp->occupancyPercent,
                'address' => $odp->address ?: 'Alamat belum diisi',
                'status' => $odp->status,
                'olt_name' => $odp->olt?->name ?? 'OLT Default',
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
