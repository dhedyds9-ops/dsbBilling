<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Attendance;
use Illuminate\Support\Facades\DB;
use Src\Domain\Workforce\Attendance as DomainAttendance;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AttendanceService
{
    public function checkIn(Uuid $technicianId, GPSCoordinate $coordinate, \DateTimeInterface $checkInTime): DomainAttendance
    {
        return DB::transaction(function () use ($technicianId, $coordinate, $checkInTime) {
            $today = new \DateTimeImmutable($checkInTime->format('Y-m-d'));
            $domainAttendance = DomainAttendance::create($technicianId, $today);
            $domainAttendance->checkIn($coordinate, $checkInTime);

            Attendance::create([
                'uuid' => $domainAttendance->id->value,
                'technician_id' => $technicianId->value,
                'date' => $today,
                'status' => $domainAttendance->status,
                'check_in_latitude' => $coordinate->latitude,
                'check_in_longitude' => $coordinate->longitude,
                'checked_in_at' => $checkInTime,
                'notes' => $domainAttendance->notes,
            ]);

            return $domainAttendance;
        });
    }

    public function checkOut(Uuid $technicianId, GPSCoordinate $coordinate, \DateTimeInterface $checkOutTime): DomainAttendance
    {
        return DB::transaction(function () use ($technicianId, $coordinate, $checkOutTime) {
            $today = new \DateTimeImmutable($checkOutTime->format('Y-m-d'));
            $attendance = Attendance::where('technician_id', $technicianId->value)
                ->where('date', $today)
                ->firstOrFail();

            $domainAttendance = new DomainAttendance(
                Uuid::fromString($attendance->uuid),
                Uuid::fromString($attendance->technician_id),
                $today,
                $attendance->status,
                $attendance->check_in_latitude ? new GPSCoordinate($attendance->check_in_latitude, $attendance->check_in_longitude) : null,
                $attendance->checked_in_at,
                null,
                null,
                $attendance->notes,
                $attendance->created_at,
                $attendance->updated_at
            );

            $domainAttendance->checkOut($coordinate, $checkOutTime);

            $attendance->update([
                'status' => $domainAttendance->status,
                'check_out_latitude' => $coordinate->latitude,
                'check_out_longitude' => $coordinate->longitude,
                'checked_out_at' => $checkOutTime,
            ]);

            return $domainAttendance;
        });
    }
}
