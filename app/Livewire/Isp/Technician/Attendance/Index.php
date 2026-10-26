<?php

namespace App\Livewire\Isp\Technician\Attendance;


use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Workforce\Attendance;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    public $latitude;
    public $longitude;
    public $notes;
    public $errorMessage;

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
    public function updateLocation($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function checkIn()
    {
        if (!$this->latitude || !$this->longitude) {
            $this->errorMessage = "Lokasi GPS tidak ditemukan. Pastikan Anda mengizinkan akses lokasi (GPS) pada browser HP Anda.";
            return;
        }

        $attendance = $this->todayAttendance;

        if ($attendance) {
            $this->errorMessage = "Anda sudah melakukan absen masuk hari ini.";
            return;
        }

        $now = Carbon::now();

        // Validasi Geofencing jika koordinat kantor/cabang sudah ditentukan di sistem
        $user = Auth::user();
        $branch = $user->branch ?? \App\Models\Master\Branch::where('is_active', true)->whereNotNull('latitude')->whereNotNull('longitude')->first();
        $distanceMeters = null;

        if ($branch && !empty($branch->latitude) && !empty($branch->longitude)) {
            $distanceMeters = $this->calculateDistanceInMeters(
                (float) $this->latitude,
                (float) $this->longitude,
                (float) $branch->latitude,
                (float) $branch->longitude
            );

            // Radius batas wajar: 150 meter dari kantor
            $maxAllowedRadius = 150;
            if ($distanceMeters > $maxAllowedRadius) {
                $this->errorMessage = "Gagal Check-In: Anda berada di luar radius kantor {$branch->name} (Jarak terdeteksi: {$distanceMeters} meter, batas toleransi: {$maxAllowedRadius} meter).";
                return;
            }
        }

        // Batas telat jam 09:00 pagi
        $status = $now->format('H:i') > '09:00' ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

        $notes = $this->notes;
        if ($distanceMeters !== null) {
            $notes = ($notes ? $notes . ' | ' : '') . "Jarak kantor: {$distanceMeters}m";
        }

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
        session()->flash('success', 'Berhasil Check-In pada ' . $now->format('H:i'));
    }

    public function checkOut()
    {
        if (!$this->latitude || !$this->longitude) {
            $this->errorMessage = "Lokasi GPS tidak ditemukan. Pastikan akses lokasi (GPS) aktif.";
            return;
        }

        $attendance = $this->todayAttendance;

        if (!$attendance) {
            $this->errorMessage = "Anda belum Check-In hari ini.";
            return;
        }

        if ($attendance->checked_out_at) {
            $this->errorMessage = "Anda sudah melakukan Check-Out.";
            return;
        }

        $attendance->update([
            'status' => AttendanceStatus::CHECKED_OUT,
            'check_out_latitude' => $this->latitude,
            'check_out_longitude' => $this->longitude,
            'checked_out_at' => Carbon::now(),
            'notes' => $attendance->notes ? $attendance->notes . ' | Checkout: ' . $this->notes : $this->notes,
        ]);

        $this->notes = '';
        $this->errorMessage = '';
        session()->flash('success', 'Berhasil Check-Out pada ' . Carbon::now()->format('H:i'));
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

        return view('livewire.isp.technician.attendance.index', [
            'attendance' => $this->todayAttendance,
            'history' => $history,
        ]);
    }
}


