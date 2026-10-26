<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Index extends BaseAdminComponent
{
    public $filterStatus = '';
    public $filterDepartment = '';
    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $deleteId = null;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Data Pegawai', 'url' => route('admin.employee.index')],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterDepartment = '';
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSingle()
    {
        if ($this->deleteId) {
            $emp = Employee::find($this->deleteId);
            if ($emp) {
                $emp->delete();
                $this->dispatch('toast', ['type' => 'success', 'message' => 'Data pegawai berhasil dihapus.']);
            }
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function bulkDelete()
    {
        if (!empty($this->selectedIds)) {
            Employee::whereIn('id', $this->selectedIds)->delete();
            $this->dispatch('toast', ['type' => 'success', 'message' => count($this->selectedIds) . ' data pegawai berhasil dihapus.']);
            $this->selectedIds = [];
            $this->selectAll = false;
        }
    }

    /**
     * Kirim data login (Nama, Jabatan, Username, Password) langsung ke nomor WhatsApp pegawai via Gateway.
     */
    public function sendWhatsAppCredentials($employeeId)
    {
        $employee = Employee::with('user')->find($employeeId);
        if (!$employee) {
            $this->dispatch('toast', type: 'error', message: 'Pegawai tidak ditemukan.');
            return;
        }

        $rawPhone = $employee->phone ?: '';
        $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '628' . substr($phone, 1);
        } elseif (str_starts_with($phone, '6208')) {
            $phone = '628' . substr($phone, 4);
        }

        if (empty($phone) || !str_starts_with($phone, '628') || strlen($phone) < 10 || strlen($phone) > 16) {
            $this->dispatch('toast', type: 'error', message: "Nomor WhatsApp pegawai ('{$rawPhone}') tidak valid. Gunakan nomor seluler Indonesia (08... atau 628...).");
            return;
        }

        // Jika belum memiliki user akun, buatkan otomatis
        if (!$employee->user_id || !$employee->user) {
            $user = User::where('email', $employee->email)->first();
            $defaultPassword = 'Password123!';
            if (!$user) {
                $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $employee->name)[0] ?? 'pegawai'));
                $username = $baseUsername;
                $c = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $c++;
                }

                $user = User::create([
                    'name' => $employee->name,
                    'email' => $employee->email ?: "{$username}@example.local",
                    'username' => $username,
                    'phone' => $phone,
                    'password' => Hash::make($defaultPassword),
                    'is_active' => true,
                ]);

                // Tautkan role staff jika ada
                $role = \App\Models\Role::whereIn('name', ['staff', 'employee', 'teknisi', 'operator'])->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }

            $employee->update(['user_id' => $user->id]);
            $employee->refresh();
        }

        $user = $employee->user;
        $username = $user?->username ?? ($user?->email ?? $employee->email);
        $password = 'Password123!';
        $jabatan = $employee->position ?: ($employee->department ?: 'Staff');
        $nama = $employee->name;

        $msgText = "Halo *{$nama}*,\n\n"
            . "Berikut informasi akun login sistem Anda:\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 *Aplikasi:* dsBilling & MStore\n"
            . "👤 *Nama:* {$nama}\n"
            . "💼 *Jabatan:* {$jabatan}\n"
            . "🌐 *Link Login:* " . url('/login') . "\n\n"
            . "🔐 *Kredensial Login:*\n"
            . "• Username: *{$username}*\n"
            . "• Password: *{$password}*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "Silakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan, segera ganti password Anda setelah login pertama kali.\n\n"
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
            Log::error('WhatsApp Gateway Error on Employee Index: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: "❌ Terjadi kendala: " . $e->getMessage());
        }
    }

    private function getFilteredQuery()
    {
        return Employee::with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDepartment !== '', fn($q) => $q->where('department', $this->filterDepartment));
    }

    public function render()
    {
        $employees = $this->getFilteredQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'with_user' => Employee::whereNotNull('user_id')->count(),
        ];

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
            'stats' => $stats,
        ]);
    }
}
