<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nik', 'name', 'position', 'department', 
        'phone', 'email', 'join_date', 'base_salary', 
        'bank_name', 'bank_account', 'status', 'user_id'
    ];

    protected $casts = [
        'join_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
