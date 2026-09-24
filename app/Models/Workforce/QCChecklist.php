<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QCChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'inspection_id',
        'item',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QCInspection::class, 'inspection_id', 'uuid');
    }

    public function result(): HasOne
    {
        return $this->hasOne(QCResult::class, 'checklist_id', 'uuid');
    }
}
