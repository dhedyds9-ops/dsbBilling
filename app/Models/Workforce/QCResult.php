<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\QCResultStatus;

class QCResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'checklist_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => QCResultStatus::class,
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(QCChecklist::class, 'checklist_id', 'uuid');
    }
}
