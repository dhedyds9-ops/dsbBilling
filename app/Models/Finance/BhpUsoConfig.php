<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhpUsoConfig extends Model
{
    use HasFactory;

    protected $table = 'bhp_uso_configs';

    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'calculation_basis',
        'bhp_rate',
        'uso_rate',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'bhp_rate' => 'decimal:4',
        'uso_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public static function getActiveForDate(string|\DateTimeInterface $date): ?self
    {
        $d = is_string($date) ? $date : $date->format('Y-m-d');
        
        return self::where('is_active', true)
            ->where('start_date', '<=', $d)
            ->where('end_date', '>=', $d)
            ->first();
    }
}
