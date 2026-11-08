<?php

namespace App\Livewire\Workforce;

use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Src\Domain\Workforce\Enums\AttendanceStatus;

class AttendancePrompt extends Component
{
    public bool $isOpen = false;
    public bool $hasCheckedIn = false;
    public bool $isWorkforce = false;
    public ?string $staffName = '';
    public ?string $staffRole = '';
    public ?string $checkInTime = null;
    public string $latitude = '';
    public string $longitude = '';
    public string $notes = '';
    public ?string $errorMessage = null;

    public function mount()
    {
        $this->evaluateAttendance();
    }

    public function evaluateAttendance(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->isWorkforce = false;
            return;
        }

        $this->staffName = $user->name;
        $this->staffRole = $user->job_title ?: ($user->job_function ?: 'Staf Operasional');

        // Administrator / Superadmin yang murni pengawas dikecualikan
        $isPureAdminOversight = $user->hasRole(['administrator', 'superadmin'])
            && ! in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS'])
            && ! str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
            && ! str_contains(strtolower((string) ($user->job_title ?? '')), 'teknisi');

        if ($isPureAdminOversight || ($user->hasRole(['customer', 'reseller']) && ! $user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service']))) {
            $this->isWorkforce = false;
            return;
        }

        // Cek apakah user adalah staf operasional yang wajib presensi
        $this->isWorkforce = $user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service'])
            || in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS', 'SALES'])
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'logistik')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'teknisi')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'kasir')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'billing')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'customer service')
            || str_contains(strtolower((string) ($user->job_title ?? '')), 'cs');

        if (! $this->isWorkforce) {
            return;
        }

        // Cek riwayat absensi hari ini
        $attendance = Attendance::where('technician_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('checked_in_at')
            ->first();

        if ($attendance) {
            $this->hasCheckedIn = true;
            $this->checkInTime = Carbon::parse($attendance->checked_in_at)->format('H:i');
            $this->isOpen = false;
        } else {
            $this->hasCheckedIn = false;
            // Jika ada sinyal session atau belum absen sama sekali di modul operasional, buka modal
            if (session('must_check_in_popup') || request()->is('inventory/*') || request()->is('billing/*') || request()->is('support/*')) {
                $this->isOpen = true;
            }
        }
    }

    #[On('open-attendance-prompt')]
    public function openModal(): void
    {
        $this->isOpen = true;
        $this->errorMessage = null;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->errorMessage = null;
    }

    #[On('gps-located')]
    public function updateLocation($lat, $lng): void
    {
        $this->latitude = (string) $lat;
        $this->longitude = (string) $lng;
    }

    public function checkIn(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->errorMessage = 'Sesi login tidak valid. Silakan muat ulang halaman.';
            return;
        }

        try {
            $existing = Attendance::where('technician_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->first();

            if ($existing && $existing->checked_in_at) {
                $this->hasCheckedIn = true;
                $this->isOpen = false;
                $this->dispatch('toast', ['type' => 'info', 'message' => 'Anda sudah melakukan presensi masuk hari ini.']);
                return;
            }

            $now = Carbon::now();
            $ip = request()->ip();

            $lat = ! empty($this->latitude) ? (float) $this->latitude : null;
            $lng = ! empty($this->longitude) ? (float) $this->longitude : null;

            // Toleransi status keterlambatan (setelah 08:30 WIB)
            $isLate = $now->format('H:i') > '08:30';
            $status = $isLate ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

            $notes = trim(($this->notes ? $this->notes . ' | ' : '') . 'IP: ' . $ip);

            if ($existing) {
                $existing->update([
                    'status' => $status,
                    'checked_in_at' => $now,
                    'check_in_latitude' => $lat,
                    'check_in_longitude' => $lng,
                    'notes' => $notes,
                ]);
            } else {
                Attendance::create([
                    'id' => (string) Str::uuid(),
                    'technician_id' => $user->id,
                    'date' => $now->toDateString(),
                    'status' => $status,
                    'check_in_latitude' => $lat,
                    'check_in_longitude' => $lng,
                    'checked_in_at' => $now,
                    'notes' => $notes,
                ]);
            }

            $this->hasCheckedIn = true;
            $this->isOpen = false;
            $this->checkInTime = $now->format('H:i');

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Presensi Masuk berhasil dicatat. Selamat bertugas!',
            ]);
            $this->dispatch('workforce-checked-in');
            $this->js('setTimeout(() => window.location.reload(), 1000);');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menyimpan presensi: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.workforce.attendance-prompt');
    }
}
