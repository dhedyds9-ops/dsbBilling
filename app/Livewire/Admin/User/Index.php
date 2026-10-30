<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\Role;
use App\Models\Setting;
use App\Models\ISP\Voucher;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filterJobFunction = '';
    public string $activeRole = 'all';
    public $filters = [];
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterJobFunction' => ['except' => ''],
        'activeRole' => ['except' => 'all'],
    ];
    
    public $showMappingModal = false;
    public $mappingUserId = null;
    public $mappingWilayah = '';
    public $mappingUser = null;
    
    public $showSummaryModal = false;
    public $summaryUser = null;
    public $summaryData = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users'],
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function setActiveRole(string $role): void
    {
        $this->activeRole = $role;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->filterJobFunction = '';
        $this->search = '';
        $this->activeRole = 'all';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openMappingModal($userId)
    {
        $this->mappingUserId = $userId;
        $this->mappingUser = UserModel::find($userId);
        $this->mappingWilayah = $this->mappingUser->wilayah ?? '';
        $this->showMappingModal = true;
    }

    public function closeMappingModal()
    {
        $this->showMappingModal = false;
        $this->mappingUserId = null;
        $this->mappingUser = null;
        $this->mappingWilayah = '';
    }

    public function saveMapping()
    {
        if ($this->mappingUser) {
            $this->mappingUser->wilayah = $this->mappingWilayah;
            $this->mappingUser->save();
            session()->flash('success', 'Mapping area berhasil disimpan.');
        }
        $this->closeMappingModal();
    }

    public function delete($id)
    {
        $user = UserModel::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri!');
            return;
        }
        $user->delete();
        session()->flash('success', 'User berhasil dihapus!');
    }

    public function export()
    {
        $filename = 'data-user-' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () {
            echo Excel::raw(
                new UserExport(
                    $this->buildFilteredQuery()->orderBy($this->sortField, $this->sortDirection)
                ),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function showSummary($userId)
    {
        $this->summaryUser = UserModel::find($userId);
        
        $vouchersHariIni = Voucher::with('serviceProfile')->where('created_by', $userId)->whereDate('activated_at', today())->get();
        $vouchersBulanIni = Voucher::with('serviceProfile')->where('created_by', $userId)->whereMonth('activated_at', today()->month)->whereYear('activated_at', today()->year)->get();
        
        $this->summaryData = [
            'income_hari_ini' => $vouchersHariIni->sum(fn($v) => $v->serviceProfile->price ?? 0),
            'income_bulan_ini' => $vouchersBulanIni->sum(fn($v) => $v->serviceProfile->price ?? 0),
            'fee_hari_ini' => $vouchersHariIni->sum('fee_seller'),
            'fee_bulan_ini' => $vouchersBulanIni->sum('fee_seller'),
            'stok_voucher' => Voucher::where('created_by', $userId)->where('status', 'available')->count(),
            'voucher_expired' => Voucher::where('created_by', $userId)->where('status', 'expired')->count(),
            'voucher_bulan_ini' => Voucher::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
            
            'jml_hotspot' => HotspotUser::where('created_by', $userId)->count(),
            'jml_ppp' => PPPoEUser::where('created_by', $userId)->count(),
            'isolir_hotspot' => HotspotUser::where('created_by', $userId)->whereIn('status', ['isolated', 'suspended'])->count(),
            'isolir_ppp' => PPPoEUser::where('created_by', $userId)->whereIn('status', ['isolated', 'suspended'])->count(),
            'hotspot_bulan_ini' => HotspotUser::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
            'ppp_bulan_ini' => PPPoEUser::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
        ];
        
        $this->showSummaryModal = true;
    }

    public function closeSummaryModal()
    {
        $this->showSummaryModal = false;
        $this->summaryUser = null;
        $this->summaryData = [];
    }

    /**
     * Kirim data login akun user langsung via WhatsApp Gateway / API.
     */
    public function sendWhatsAppCredentials($userId)
    {
        $user = UserModel::with(['roles', 'employee'])->find($userId);
        if (!$user) {
            $this->dispatch('toast', type: 'error', message: 'User tidak ditemukan.');
            return;
        }

        $rawPhone = $user->whatsapp ?: ($user->employee?->phone ?: ($user->phone ?? ''));
        $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '628' . substr($phone, 1);
        } elseif (str_starts_with($phone, '6208')) {
            $phone = '628' . substr($phone, 4);
        }

        if (empty($phone) || !str_starts_with($phone, '628') || strlen($phone) < 10 || strlen($phone) > 16) {
            $this->dispatch('toast', type: 'error', message: "Nomor WhatsApp user ('{$rawPhone}') tidak valid. Harap lengkapi nomor seluler Indonesia (08... atau 628...).");
            return;
        }

        $roleNames = $user->roles->pluck('display_name')->implode(', ') ?: ($user->roles->pluck('name')->implode(', ') ?: 'User');
        $jabatan = $user->job_title ?: ($user->job_function ?: $roleNames);
        $username = $user->username ?: $user->email;
        $nama = $user->name;

        $msgText = "Halo *{$nama}*,\n\n"
            . "Berikut informasi akun login sistem Anda:\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 *Aplikasi:* dsBilling & MStore\n"
            . "👤 *Nama:* {$nama}\n"
            . "💼 *Peran/Jabatan:* {$jabatan}\n"
            . "🌐 *Link Login:* " . url('/login') . "\n\n"
            . "🔐 *Kredensial Login:*\n"
            . "• Username / Email: *{$username}*\n"
            . "• Password: *Password123!*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "Silakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan akun, segera ganti password Anda setelah login pertama kali.\n\n"
            . "Terima kasih.";

        // Ambil konfigurasi WhatsApp Gateway dari database dsBilling
        $conn = Setting::getValue('whatsapp.connection', []);
        $provider = $conn['provider'] ?? 'fonnte';
        $apiToken = trim((string)($conn['api_token'] ?? ''));
        $baseUrl = trim((string)($conn['base_url'] ?? ''));

        if (empty($apiToken)) {
            $this->dispatch('toast', type: 'error', message: "WhatsApp Gateway belum dikonfigurasi (Token API masih kosong). Silakan atur di menu Pengaturan > WhatsApp.");
            return;
        }

        try {
            $sent = false;
            $errorMsg = null;

            if ($provider === 'fonnte') {
                $url = $baseUrl ?: 'https://api.fonnte.com/send';
                $resp = Http::timeout(15)
                    ->withHeaders(['Authorization' => $apiToken])
                    ->asForm()
                    ->post($url, [
                        'target' => $phone,
                        'message' => $msgText,
                        'delay' => (string)($conn['delay_per_message'] ?? '0'),
                    ]);

                if ($resp->successful()) {
                    $json = $resp->json();
                    if (($json['status'] ?? true) === false) {
                        $errorMsg = $json['reason'] ?? ($json['message'] ?? 'Fonnte mengembalikan status false.');
                    } else {
                        $sent = true;
                    }
                } else {
                    $errorMsg = 'HTTP ' . $resp->status() . ': ' . $resp->body();
                }
            } elseif ($provider === 'wablas') {
                $url = $baseUrl ?: 'https://jogja.wablas.com/api/send-message';
                $resp = Http::timeout(15)
                    ->withHeaders(['Wablas-Api-Key' => $apiToken])
                    ->asForm()
                    ->post($url, [
                        'phone' => $phone,
                        'message' => $msgText,
                    ]);

                if ($resp->successful()) {
                    $sent = true;
                } else {
                    $errorMsg = 'HTTP ' . $resp->status() . ': ' . $resp->body();
                }
            } elseif ($provider === 'whacenter') {
                $url = $baseUrl ?: 'https://app.whacenter.com/api/send';
                $resp = Http::timeout(15)
                    ->asForm()
                    ->post($url, [
                        'device_id' => $conn['device_id'] ?? '',
                        'number' => $phone,
                        'message' => $msgText,
                    ]);

                if ($resp->successful()) {
                    $sent = true;
                } else {
                    $errorMsg = 'HTTP ' . $resp->status() . ': ' . $resp->body();
                }
            } else {
                if (class_exists(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)) {
                    $waMsg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                        toPhone: $phone,
                        type: 'text',
                        text: $msgText,
                        category: 'info',
                        priorityHigh: true,
                    );
                    $sent = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->sendImmediate($waMsg);
                }
            }

            if ($sent) {
                $this->dispatch('toast', type: 'success', message: "✅ Data login berhasil dikirim otomatis via WhatsApp Gateway ke {$phone}!");
            } else {
                $this->dispatch('toast', type: 'error', message: "❌ Gagal kirim via WhatsApp Gateway: " . ($errorMsg ?: 'Perangkat/Gateway tidak merespons.'));
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp Gateway Error on User Index: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: "❌ Terjadi kendala: " . $e->getMessage());
        }
    }

    /**
     * Membangun query user terfilter yang digunakan bersama oleh tabel render dan fungsi export.
     */
    protected function buildFilteredQuery()
    {
        $query = UserModel::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['customer', 'pppoe', 'hotspot']);
            })
            ->with(['roles', 'employee', 'branch']);

        // Filter berdasarkan tab role aktif
        if ($this->activeRole === 'unassigned') {
            $query->whereDoesntHave('roles');
        } elseif ($this->activeRole !== 'all') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->activeRole);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterJobFunction !== '') {
            $query->where('job_function', $this->filterJobFunction);
        }

        return $query;
    }

    public function render()
    {
        $baseQuery = UserModel::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['customer', 'pppoe', 'hotspot']);
            });

        // Hitung total dan count per role untuk tab badges
        $totalUsersCount = (clone $baseQuery)->count();
        $unassignedCount = (clone $baseQuery)->whereDoesntHave('roles')->count();

        $roleList = Role::whereNotIn('name', ['customer', 'pppoe', 'hotspot'])
            ->orderBy('name')
            ->get()
            ->map(function ($role) use ($baseQuery) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?: ucfirst($role->name),
                    'count' => (clone $baseQuery)->whereHas('roles', fn($q) => $q->where('id', $role->id))->count(),
                ];
            });

        // Query tabel utama dengan filter & sorting
        $users = $this->buildFilteredQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage === 'all' ? 999999 : $this->perPage);

        return view('livewire.admin.user.index', [
            'users' => $users,
            'roleList' => $roleList,
            'totalUsersCount' => $totalUsersCount,
            'unassignedCount' => $unassignedCount,
        ]);
    }
}
