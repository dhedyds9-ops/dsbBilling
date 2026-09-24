<?php

declare(strict_types=1);

namespace App\Models\ISP;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VoucherTemplateAsset extends Model
{
    use HasFactory;

    protected $table = 'voucher_template_assets';

    protected $fillable = [
        'voucher_template_id',
        'name',
        'type',
        'file_path',
        'mime_type',
        'size_kb',
        'created_by',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(VoucherTemplate::class, 'voucher_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }
}
