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
     * Kirim data login (Nama, Jabatan, Username, Password) ke nomor WhatsApp karyawan.
     */
    public function sendWhatsAppCredentials()
    {
        $rawPhone = $this->phone ?: ($this->employee->phone ?? '');
        $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        }

        if (empty($phone) || strlen($phone) < 9) {
            session()->flash('error', 'Nomor WhatsApp karyawan belum diisi atau tidak valid.');
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

        $sentViaGateway = false;
        try {
            if (class_exists(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)) {
                $waMsg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                    toPhone: $phone,
                    type: 'text',
                    text: $msgText,
                    category: 'info',
                    priorityHigh: true,
                );
                $sentViaGateway = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->sendImmediate($waMsg);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp Gateway send failed: ' . $e->getMessage());
        }

        if ($sentViaGateway) {
            session()->flash('success', "Data login (Nama, Jabatan, Username, Password) berhasil dikirim via WhatsApp Gateway ke {$phone}!");
        } else {
            $waUrl = "https://wa.me/{$phone}?text=" . rawurlencode($msgText);
            $this->dispatch('open-wa-window', url: $waUrl);
            session()->flash('success', "Pesan telah disiapkan! Mengarahkan ke WhatsApp ke nomor {$phone}...");
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
