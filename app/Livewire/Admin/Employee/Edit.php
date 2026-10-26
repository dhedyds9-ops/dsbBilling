<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\AdminComponent;
use App\Models\Employee;

class Edit extends AdminComponent
{
    public Employee $employee;
    public $nik;
    public $name;
    public $position;
    public $department;
    public $phone;
    public $email;
    public $join_date;
    public $base_salary;
    public $bank_name;
    public $bank_account;
    public $status;
    public $user_id;



    public function rules()
    {
        return [
            'nik' => 'required|unique:employees,nik,' . $this->employee->id,
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => ['nullable', new \Illuminate\Validation\Rules\Enum(\App\Enums\JobFunction::class)],
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'join_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Data Pegawai', 'url' => route('admin.employee.index')],
            ['label' => 'Edit Pegawai'],
        ];

        $this->employee = \App\Models\Employee::findOrFail($id);
        $employee = $this->employee;
        $this->nik = $employee->nik;
        $this->name = $employee->name;
        $this->position = $employee->position;
        $this->department = $employee->department;
        $this->phone = $employee->phone;
        $this->email = $employee->email;
        $this->join_date = $employee->join_date;
        $this->base_salary = $employee->base_salary;
        $this->bank_name = $employee->bank_name;
        $this->bank_account = $employee->bank_account;
        $this->status = $employee->status;
        $this->user_id = $employee->user_id;
    }

    public function save()
    {
        $this->validate();

        $this->employee->update([
            'nik' => $this->nik,
            'name' => $this->name,
            'position' => $this->position,
            'department' => $this->department ?: null,
            'phone' => $this->phone,
            'email' => $this->email,
            'join_date' => $this->join_date ?: null,
            'base_salary' => $this->base_salary ?: 0,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'status' => $this->status,
            'user_id' => $this->user_id ?: null,
        ]);

        if ($this->user_id && $this->department) {
            \App\Models\User::where('id', $this->user_id)->update(['job_function' => $this->department]);
        }

        session()->flash('success', 'Data pegawai berhasil diperbarui.');

        return redirect()->route('admin.employee.index');
    }

    public function createUserAccount()
    {
        try {
            $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '', $this->name)));
            $username = $baseName ?: 'user' . rand(100, 999);
            $counter = 1;
            while (\App\Models\User::where('username', $username)->exists()) {
                $username = $baseName . $counter++;
            }

            $userEmail = ! empty($this->email) ? strtolower(trim($this->email)) : ($username . '@mstore.local');
            $defaultPassword = 'Password123!';

            $newUser = \App\Models\User::create([
                'name' => $this->name,
                'email' => $userEmail,
                'username' => $username,
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
                'job_title' => $this->position ?: 'Staff',
                'job_function' => $this->department ?: \App\Enums\JobFunction::TECHNICIAN->value,
                'whatsapp' => $this->phone,
                'identity_number' => $this->nik ?: '-',
                'is_active' => true,
            ]);

            $managerRole = \App\Models\Role::where('name', \App\Enums\UserRole::Manager->value ?? 'manager')->first();
            if ($managerRole) {
                $newUser->roles()->sync([$managerRole->id]);
            }

            $this->user_id = $newUser->id;
            $this->employee->update(['user_id' => $newUser->id]);

            session()->flash('success', "Akun login berhasil dibuat! Username: {$username} | Password: {$defaultPassword}");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membuat akun login: ' . $e->getMessage());
        }
    }

    /**
     * Kirim data login (Nama, Jabatan, Username, Password) langsung ke nomor WhatsApp karyawan via Gateway.
     */
    public function sendWhatsAppCredentials()
    {
        $rawPhone = $this->phone ?: ($this->employee->phone ?? '');
        $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '628' . substr($phone, 1);
        } elseif (str_starts_with($phone, '6208')) {
            $phone = '628' . substr($phone, 4);
        }

        if (empty($phone) || !str_starts_with($phone, '628') || strlen($phone) < 10 || strlen($phone) > 16) {
            session()->flash('error', "Nomor WhatsApp karyawan ('{$rawPhone}') tidak valid. WhatsApp memerlukan nomor ponsel Indonesia aktif (contoh: 08123456789 atau 628123456789). Silakan perbarui nomor telepon pegawai.");
            return;
        }

        // Pastikan akun user sudah dibuat
        if (empty($this->user_id)) {
            $this->createUserAccount();
        }

        $user = $this->employee->fresh()->user;
        $username = $user?->username ?? ($user?->email ?? $this->email);
        $password = 'Password123!';
        $jabatan = $this->position ?: ($this->department ?: 'Staff');
        $nama = $this->name;

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

        // 1. Ambil pengaturan WhatsApp Gateway dari database dsBilling
        $conn = \App\Models\Setting::getValue('whatsapp.connection', []);
        $provider = $conn['provider'] ?? 'fonnte';
        $apiToken = trim((string)($conn['api_token'] ?? ''));
        $baseUrl = trim((string)($conn['base_url'] ?? ''));

        if (empty($apiToken)) {
            session()->flash('error', "WhatsApp Gateway belum dikonfigurasi (Token API masih kosong). Silakan atur di menu Pengaturan > WhatsApp agar pesan dapat terkirim otomatis.");
            return;
        }

        try {
            $sent = false;
            $errorMsg = null;

            // Direct Gateway Post sesuai provider aktif
            if ($provider === 'fonnte') {
                $url = $baseUrl ?: 'https://api.fonnte.com/send';
                $resp = \Illuminate\Support\Facades\Http::timeout(15)
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
                $resp = \Illuminate\Support\Facades\Http::timeout(15)
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
                $resp = \Illuminate\Support\Facades\Http::timeout(15)
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
                // Provider lainnya, coba lewat WhatsAppNotificationService jika ada
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
                session()->flash('success', "✅ Kredensial login (Username & Password) berhasil dikirim otomatis via WhatsApp Gateway ke nomor {$phone}!");
            } else {
                session()->flash('error', "❌ Gagal mengirim via WhatsApp Gateway: " . ($errorMsg ?: 'Perangkat/Gateway tidak merespons.') . " Silakan cek status koneksi di menu Pengaturan > WhatsApp.");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Gateway Exception: ' . $e->getMessage());
            session()->flash('error', "❌ Terjadi kendala saat menghubungi WhatsApp Gateway: " . $e->getMessage());
        }
    }

    public function render()
    {
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();
        return view('livewire.admin.employee.edit', compact('users'));
    }
}
