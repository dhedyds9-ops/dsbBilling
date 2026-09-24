<?php

namespace App\Livewire\NOC;

use Livewire\Component;
use App\Models\Workforce\Attendance;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceWidget extends Component
{
    public function getTodayAttendanceProperty()
    {
        return Attendance::where('technician_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();
    }

    public function checkIn()
    {
        $attendance = $this->todayAttendance;

        if (!$attendance) {
            $now = Carbon::now();
            $status = $now->format('H:i') > '09:00' ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;
            
            // Record IP Address inside notes
            $ip = request()->ip();

            Attendance::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'technician_id' => Auth::id(),
                'date' => $now->toDateString(),
                'status' => $status,
                'checked_in_at' => $now,
                'notes' => 'IP: ' . $ip,
            ]);

            $this->dispatch('toast', ['type' => 'success', 'message' => 'NOC Check-In berhasil.']);
        }
    }

    public function checkOut()
    {
        $attendance = $this->todayAttendance;

        if ($attendance && !$attendance->checked_out_at) {
            $attendance->update([
                'status' => AttendanceStatus::CHECKED_OUT,
                'checked_out_at' => Carbon::now(),
            ]);

            $this->dispatch('toast', ['type' => 'success', 'message' => 'NOC Check-Out berhasil.']);
        }
    }

    public function render()
    {
        return view('livewire.noc.attendance-widget', [
            'attendance' => $this->todayAttendance,
        ]);
    }
}
