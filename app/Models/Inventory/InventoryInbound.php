<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryInbound extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $table = 'inventory_inbounds';

    protected $fillable = [
        'uuid',
        'inbound_number',
        'warehouse_id',
        'branch_id',
        'source_type', // supplier, purchase, initial_stock, other
        'supplier_name',
        'reference_doc',
        'received_date',
        'status', // draft, received, cancelled
        'notes',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($inbound) {
            if (empty($inbound->uuid)) {
                $inbound->uuid = (string) Str::uuid();
            }
            if (empty($inbound->inbound_number)) {
                $inbound->inbound_number = self::generateInboundNumber();
            }
            if (empty($inbound->received_by) && auth()->check()) {
                $inbound->received_by = auth()->id();
            }
        });
    }

    public static function generateInboundNumber(): string
    {
        $prefix = 'INB-' . date('Ym') . '-';
        $last = self::withoutGlobalScopes()
            ->where('inbound_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('inbound_number');

        $nextSeq = 1;
        if ($last) {
            $lastSeq = (int) substr($last, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryInboundItem::class, 'inbound_id');
    }
}
