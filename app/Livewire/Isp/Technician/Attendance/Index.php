<?php

namespace App\Livewire\Isp\Technician\Attendance;


use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Workforce\Attendance;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

#[Layout('layouts.technician-app')]
#[Title('Absensi & Lokasi')]
class Index extends Component
{
    public $latitude;
    public $longitude;
    public $notes;
    public $errorMessage;
    public bool $isFieldAssignment = false; // Mode tugas luar / lapangan
    public bool $confirmingCheckOut = false; // Dialog konfirmasi checkout

    public function mount()
    {
        // ready
    }

    public function getTodayAttendanceProperty()
    {
        return Attendance::where('technician_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();
    }

    #[On('gps-located')]
    public function updateLocation($lat = null, $lng = null)
    {
        if (is_array($lat)) {
            $this->latitude = $lat['lat'] ?? $lat['latitude'] ?? null;
            $this->longitude = $lat['lng'] ?? $lat['longitude'] ?? null;
            return;
        }
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function checkIn()
    {
        $attendance = $this->todayAttendance;

        if ($attendance) {
            $this->errorMessage = "Anda sudah melakukan absen masuk hari ini.";
            return;
        }

        $ip = request()->ip();

        // Validasi GPS: jika tidak ada GPS dan bukan penugasan lapangan, minta aktifkan GPS atau centang tugas luar
        if ((empty($this->latitude) || empty($this->longitude)) && !$this->isFieldAssignment) {
            $this->errorMessage = "Sinyal lokasi GPS belum terdeteksi. Pastikan GPS HP Anda aktif dan izin lokasi browser telah diizinkan. Jika sedang di area tanpa sinyal GPS, aktifkan opsi 'Tugas Luar / Lapangan'.";
            return;
        }

        $now = Carbon::now();
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin']));
        $homeBranch = $user->branch;

        // 1. SMART MULTI-BRANCH GEOFENCING:
        // Cek home branch dan seluruh cabang aktif perusahaan yang memiliki koordinat GPS
        $branches = \App\Models\Master\Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($homeBranch && !empty($homeBranch->latitude) && !empty($homeBranch->longitude)) {
            if (!$branches->contains('id', $homeBranch->id)) {
                $branches->push($homeBranch);
            }
        }

        $nearestBranch = null;
        $minDistance = null;
        $maxAllowedRadius = 150; // Toleransi radius 150 meter dari titik kantor

        if (!empty($this->latitude) && !empty($this->longitude)) {
            foreach ($branches as $br) {
                $dist = $this->calculateDistanceInMeters(
                    (float) $this->latitude,
                    (float) $this->longitude,
                    (float) $br->latitude,
                    (float) $br->longitude
                );

                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $nearestBranch = $br;
                }
            }
        }

        $matchedBranch = null;
        if ($nearestBranch && $minDistance !== null && $minDistance <= $maxAllowedRadius) {
            $matchedBranch = $nearestBranch;
        }

        // Jika ada cabang terdaftar dengan koordinat GPS, validasi radius:
        if (!$isAdmin && !$matchedBranch && $branches->isNotEmpty()) {
            if ($this->isFieldAssignment) {
                if (empty(trim($this->notes ?? ''))) {
                    $this->errorMessage = "Presensi Tugas Luar / Lapangan memerlukan catatan (contoh: No. SPK, nama pelanggan, atau keperluan penugasan).";
                    return;
                }
            } else {
                $nearestText = $nearestBranch 
                    ? " Cabang terdekat adalah {$nearestBranch->name} (jarak terdeteksi: {$minDistance} meter, batas toleransi: {$maxAllowedRadius} meter)."
                    : "";

                $this->errorMessage = "Gagal Check-In: Anda berada di luar radius kantor perusahaan.{$nearestText} Jika Anda ditugaskan langsung ke lapangan/pelanggan, aktifkan opsi 'Tugas Luar / Lapangan' dan cantumkan keterangannya.";
                return;
            }
        }

        // Batas telat jam 09:00 pagi
        $status = $now->format('H:i') > '09:00' ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

        $notes = $this->notes ? trim($this->notes) : '';
        if ($matchedBranch) {
            $isCross = $homeBranch && $matchedBranch->id !== $homeBranch->id;
            $locationNote = $isCross 
                ? "Lokasi: Lintas Cabang {$matchedBranch->name} (Home: {$homeBranch->name}) · {$minDistance}m"
                : "Lokasi: {$matchedBranch->name} · {$minDistance}m";
            $notes = ($notes ? $notes . ' | ' : '') . $locationNote;
        } elseif ($this->isFieldAssignment) {
            $notes = ($notes ? $notes . ' | ' : '') . "Mode: Penugasan Lapangan / Tugas Luar";
        }
        $notes = ($notes ? $notes . ' | ' : '') . "IP: {$ip}";

        Attendance::create([
            'id' => Str::uuid()->toString(),
            'technician_id' => Auth::id(),
            'date' => Carbon::today(),
            'status' => $status,
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'checked_in_at' => $now,
            'notes' => $notes,
        ]);

        $this->notes = '';
        $this->errorMessage = '';
        $this->isFieldAssignment = false;
        session()->flash('success', 'Berhasil Check-In pada ' . $now->format('H:i') . ($matchedBranch ? " di {$matchedBranch->name}" : ''));
    }

    public function promptCheckOut()
    {
        $this->errorMessage = '';
        $attendance = $this->todayAttendance;

        if (!$attendance) {
            $this->errorMessage = "Anda belum Check-In hari ini.";
            return;
        }

        if ($attendance->checked_out_at) {
            $this->errorMessage = "Anda sudah melakukan Check-Out hari ini.";
            return;
        }

        // TIME-LOCK SAFEGUARD: Minimal 15 menit berdinas untuk mencegah salah pencet setelah check-in
        $minutesWorked = $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->diffInMinutes(Carbon::now()) : 0;
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin']));

        if (!$isAdmin && $minutesWorked < 15) {
            $remaining = 15 - $minutesWorked;
            $this->errorMessage = "Pencegahan Salah Pencet: Anda baru saja Check-In ({$minutesWorked} menit lalu). Tombol Check-Out baru dapat digunakan minimal 15 menit setelah Check-In (tersisa {$remaining} menit lagi).";
            return;
        }

        $this->confirmingCheckOut = true;
    }

    public function cancelCheckOut()
    {
        $this->confirmingCheckOut = false;
    }

    public function checkOut()
    {
        $attendance = $this->todayAttendance;

        if (!$attendance) {
            $this->errorMessage = "Anda belum Check-In hari ini.";
            $this->confirmingCheckOut = false;
            return;
        }

        if ($attendance->checked_out_at) {
            $this->errorMessage = "Anda sudah melakukan Check-Out.";
            $this->confirmingCheckOut = false;
            return;
        }

        // Time lock check (minimal 15 menit berdinas, kecuali admin)
        $minutesWorked = $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->diffInMinutes(Carbon::now()) : 0;
        $user = Auth::user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin']));

        if (!$isAdmin && $minutesWorked < 15) {
            $remaining = 15 - $minutesWorked;
            $this->errorMessage = "Pencegahan Salah Pencet: Anda baru saja Check-In ({$minutesWorked} menit lalu). Tersisa {$remaining} menit lagi sebelum dapat Check-Out.";
            $this->confirmingCheckOut = false;
            return;
        }

        $now = Carbon::now();
        $ip = request()->ip();
        $checkoutNote = 'Checkout IP: ' . $ip . ($this->notes ? ' | ' . trim($this->notes) : '');

        $attendance->update([
            'status' => AttendanceStatus::CHECKED_OUT,
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'checked_out_at' => $now,
            'notes' => $attendance->notes ? $attendance->notes . ' | ' . $checkoutNote : $checkoutNote,
        ]);

        $this->notes = '';
        $this->errorMessage = '';
        $this->confirmingCheckOut = false;
        session()->flash('success', 'Berhasil Check-Out pada ' . $now->format('H:i') . '. Terima kasih atas kerja keras Anda hari ini!');
    }

    /**
     * Hitung jarak lurus (Haversine distance) antara 2 titik koordinat dalam meter.
     */
    private function calculateDistanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Radius bumi dalam satuan meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }

    public function render()
    {
        $history = Attendance::where('technician_id', Auth::id())
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $branches = \App\Models\Master\Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude']);

        $homeBranch = Auth::user()?->branch;

        return view('livewire.isp.technician.attendance.index', [
            'attendance' => $this->todayAttendance,
            'history' => $history,
            'branches' => $branches,
            'homeBranch' => $homeBranch,
        ]);
    }
}


