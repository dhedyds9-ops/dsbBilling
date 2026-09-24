<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id', 'period_month', 'period_year',
        'period_start', 'period_end',
        'base_salary', 'allowances', 'deductions', 
        'net_salary', 'status', 'payment_date', 'notes'
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
