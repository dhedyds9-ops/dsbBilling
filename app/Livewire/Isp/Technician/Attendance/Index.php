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
        // Batas telat jam 09:00 pagi
        $status = $now->format('H:i') > '09:00' ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

        Attendance::create([
            'id' => Str::uuid()->toString(),
            'technician_id' => Auth::id(),
            'date' => Carbon::today(),
            'status' => $status,
            'check_in_latitude' => $this->latitude,
            'check_in_longitude' => $this->longitude,
            'checked_in_at' => $now,
            'notes' => $this->notes,
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


