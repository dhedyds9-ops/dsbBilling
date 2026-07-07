<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\Attendance as AttendanceModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Attendance;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Src\Domain\Workforce\Repositories\AttendanceRepositoryInterface;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(AttendanceModel $model)
    {
        parent::__construct($model);
    }

    public function save(Attendance $attendance): Attendance
    {
        $this->model->updateOrCreate(
            ['uuid' => $attendance->id->value],
            [
                'technician_id' => $attendance->technicianId->value,
                'date' => $attendance->date,
                'status' => $attendance->status,
                'check_in_latitude' => $attendance->checkInLocation?->latitude,
                'check_in_longitude' => $attendance->checkInLocation?->longitude,
                'checked_in_at' => $attendance->checkedInAt,
                'check_out_latitude' => $attendance->checkOutLocation?->latitude,
                'check_out_longitude' => $attendance->checkOutLocation?->longitude,
                'checked_out_at' => $attendance->checkedOutAt,
                'notes' => $attendance->notes,
            ]
        );

        return $attendance;
    }

    public function findById(Uuid $id): ?Attendance
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new Attendance(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            $model->date->toDateTimeImmutable(),
            $model->status,
            $model->check_in_latitude ? new GPSCoordinate($model->check_in_latitude, $model->check_in_longitude) : null,
            $model->checked_in_at?->toDateTimeImmutable(),
            $model->check_out_latitude ? new GPSCoordinate($model->check_out_latitude, $model->check_out_longitude) : null,
            $model->checked_out_at?->toDateTimeImmutable(),
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByTechnicianAndDate(Uuid $technicianId, \DateTimeImmutable $date): ?Attendance
    {
        $model = $this->model->where('technician_id', $technicianId->value)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        if (!$model) {
            return null;
        }

        return new Attendance(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->technician_id),
            $model->date->toDateTimeImmutable(),
            $model->status,
            $model->check_in_latitude ? new GPSCoordinate($model->check_in_latitude, $model->check_in_longitude) : null,
            $model->checked_in_at?->toDateTimeImmutable(),
            $model->check_out_latitude ? new GPSCoordinate($model->check_out_latitude, $model->check_out_longitude) : null,
            $model->checked_out_at?->toDateTimeImmutable(),
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }
}
