<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'owner_settlement_price',
        'branch_settlement_price',
        'reseller_settlement_price',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'owner_settlement_price' => 'decimal:2',
        'branch_settlement_price' => 'decimal:2',
        'reseller_settlement_price' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
