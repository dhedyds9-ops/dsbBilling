<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Domain\Workforce\Enums\QCStatus;

class QCInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'task_id',
        'inspector_id',
        'status',
        'notes',
        'inspected_at',
    ];

    protected $casts = [
        'status' => QCStatus::class,
        'inspected_at' => 'datetime',
    ];

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(QCChecklist::class, 'inspection_id', 'uuid');
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(QCApproval::class, 'id', 'inspection_id');
    }
}
