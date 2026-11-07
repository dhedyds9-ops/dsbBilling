<?php

namespace App\Models\Inventory;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryStockMutation extends Model
{
    use HasFactory, HasBranchScope;

    public $timestamps = false; // Only created_at is used for ledger

    protected $table = 'inventory_stock_mutations';

    protected $fillable = [
        'uuid',
        'mutation_number',
        'warehouse_id',
        'item_id',
        'type', // inbound, outbound_dispatch, transfer_out, transfer_in, usage, return_good, return_damaged, adjustment_in, adjustment_out
        'qty_in',
        'qty_out',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'item_serial_id',
        'batch_number',
        'notes',
        'actor_id',
        'branch_id',
        'created_at',
    ];

    protected $casts = [
        'qty_in' => 'decimal:2',
        'qty_out' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($mutation) {
            if (empty($mutation->uuid)) {
                $mutation->uuid = (string) Str::uuid();
            }
            if (empty($mutation->mutation_number)) {
                $mutation->mutation_number = self::generateMutationNumber();
            }
            if (empty($mutation->created_at)) {
                $mutation->created_at = now();
            }
            if (empty($mutation->actor_id) && auth()->check()) {
                $mutation->actor_id = auth()->id();
            }
        });
    }

    public static function generateMutationNumber(): string
    {
        $prefix = 'MUT-' . date('Ym') . '-';
        $last = self::withoutGlobalScopes()
            ->where('mutation_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('mutation_number');

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

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ItemSerial::class, 'item_serial_id');
    }

    public function itemSerial(): BelongsTo
    {
        return $this->belongsTo(ItemSerial::class, 'item_serial_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function isCredit(): bool
    {
        return $this->qty_in > 0;
    }

    public function isDebit(): bool
    {
        return $this->qty_out > 0;
    }
}
